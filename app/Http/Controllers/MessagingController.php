<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MessagingController extends Controller
{
    /**
     * List user's conversations (clinic-scoped)
     */
    public function conversations(Request $request)
    {
        $user = $request->user();
        $conversations = Conversation::with(['participants.user:id,first_name,last_name,role', 'messages' => function($q){ $q->latest()->limit(1); }])
            ->forClinic($user->clinic_id)
            ->forUser($user->id)
            ->orderByDesc('last_message_at')
            ->limit(50)
            ->get()
            ->map(function($c) use ($user) {
                $unread = MessageRecipient::whereIn('message_id', $c->messages()->pluck('id'))
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();
                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'type' => $c->type,
                    'last_message_at' => $c->last_message_at,
                    'unread' => $unread,
                    'participants' => $c->participants->map(fn($p) => [
                        'id' => $p->user->id,
                        'name' => $p->user->first_name . ' ' . $p->user->last_name,
                        'role' => $p->user->role,
                    ]),
                    'last_message' => optional($c->messages->first())->only(['id','message_type','body','created_at'])
                ];
            });
        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    /**
     * Create a new conversation
     */
    public function createConversation(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'integer|exists:users,id',
        ]);

        // Enforce same clinic
        $participantIds = array_values(array_unique(array_filter($data['participant_ids'], fn($id) => $id != $user->id)));
        $participants = User::whereIn('id', $participantIds)->where('clinic_id', $user->clinic_id)->pluck('id')->all();

        if (count($participants) !== count($participantIds)) {
            abort(403, 'All participants must belong to your clinic');
        }

        return DB::transaction(function() use ($request, $user, $data, $participants) {
            $type = count($participants) === 1 ? 'direct' : 'group';
            $conversation = Conversation::create([
                'clinic_id' => $user->clinic_id,
                'type' => $type,
                'title' => $data['title'] ?? null,
                'created_by' => $user->id,
                'last_message_at' => now(),
            ]);

            // Add creator and participants
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'joined_at' => now(),
            ]);
            foreach ($participants as $pid) {
                ConversationParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $pid,
                    'role' => 'member',
                    'joined_at' => now(),
                ]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->full_name ?? ($user->first_name.' '.$user->last_name),
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => 'create_conversation',
                'model_type' => Conversation::class,
                'model_id' => $conversation->id,
                'description' => 'Conversation created',
                'new_values' => ['participants' => array_merge([$user->id], $participants)],
                'performed_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['success' => true, 'conversation_id' => $conversation->id]);
        });
    }

    /**
     * Send a message (text or transfer)
     */
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'conversation_id' => 'required|integer|exists:conversations,id',
            'body' => 'nullable|string',
            'patient_id' => 'nullable|integer|exists:patients,id',
            'is_transfer' => 'nullable|boolean',
            'transfer_type' => 'nullable|string|in:patient_file,radiology_request,lab_request,lab_result,prescription,nutrition_plan',
            'source_type' => 'nullable|string',
            'source_id' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

        $conversation = Conversation::with('participants')->findOrFail($data['conversation_id']);
        if ($conversation->clinic_id !== $user->clinic_id) abort(403);
        if (!$conversation->participants->pluck('user_id')->contains($user->id)) abort(403);

        // Optional patient association must match clinic
        if (!empty($data['patient_id'])) {
            $patient = Patient::findOrFail($data['patient_id']);
            if ($patient->clinic_id !== $user->clinic_id) abort(403);
        }

        return DB::transaction(function() use ($request, $user, $conversation, $data) {
            $isTransfer = (bool)($data['is_transfer'] ?? false);
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $user->id,
                'clinic_id' => $user->clinic_id,
                'patient_id' => $data['patient_id'] ?? null,
                'message_type' => $isTransfer ? 'transfer' : 'text',
                'body' => $isTransfer ? null : ($data['body'] ?? ''),
                'metadata' => $data['metadata'] ?? null,
            ]);

            // Update conversation last activity
            $conversation->update(['last_message_at' => now()]);

            // Create recipients for all other participants
            $recipientIds = $conversation->participants->pluck('user_id')->filter(fn($id) => $id !== $user->id)->values();
            foreach ($recipientIds as $rid) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'user_id' => $rid,
                    'delivered_at' => now(),
                ]);
            }

            // Create transfer if needed
            if ($isTransfer) {
                if (empty($data['transfer_type']) || empty($data['source_type']) || empty($data['source_id']) || empty($data['patient_id'])) {
                    abort(422, 'Transfer requires transfer_type, source_type, source_id and patient_id');
                }
                Transfer::create([
                    'clinic_id' => $user->clinic_id,
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id,
                    'patient_id' => $data['patient_id'],
                    'transfer_type' => $data['transfer_type'],
                    'source_type' => $data['source_type'],
                    'source_id' => $data['source_id'],
                    'status' => 'pending',
                    'metadata' => $data['metadata'] ?? null,
                ]);
            }

            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->full_name ?? ($user->first_name.' '.$user->last_name),
                'user_role' => $user->role,
                'clinic_id' => $user->clinic_id,
                'action' => $isTransfer ? 'send_transfer' : 'send_message',
                'model_type' => Message::class,
                'model_id' => $message->id,
                'description' => $isTransfer ? 'Transfer sent' : 'Message sent',
                'new_values' => ['conversation_id' => $conversation->id],
                'performed_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['success' => true, 'message_id' => $message->id]);
        });
    }

    /**
     * Mark all messages in a conversation as read for current user
     */
    public function markRead(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        if ($conversation->clinic_id !== $user->clinic_id) abort(403);
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) abort(403);

        MessageRecipient::whereIn('message_id', $conversation->messages()->pluck('id'))
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->participants()->where('user_id', $user->id)->update(['last_read_at' => now()]);
        return response()->json(['success' => true]);
    }

    /**
     * Get unread count for current user
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = MessageRecipient::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
        return response()->json(['success' => true, 'unread' => $count]);
    }

    /**
     * Accept / Reject / Acknowledge a transfer
     */
    public function transferAction(Request $request, Transfer $transfer)
    {
        $user = $request->user();
        if ($transfer->clinic_id !== $user->clinic_id) abort(403);

        $data = $request->validate([
            'action' => 'required|string|in:accept,reject,acknowledge',
        ]);

        $newStatus = match($data['action']) {
            'accept' => 'accepted',
            'reject' => 'rejected',
            'acknowledge' => 'acknowledged',
        };

        $transfer->update([
            'status' => $newStatus,
            'acted_by' => $user->id,
            'acted_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->full_name ?? ($user->first_name.' '.$user->last_name),
            'user_role' => $user->role,
            'clinic_id' => $user->clinic_id,
            'action' => 'transfer_'.$newStatus,
            'model_type' => Transfer::class,
            'model_id' => $transfer->id,
            'description' => 'Transfer '.$newStatus,
            'new_values' => ['status' => $newStatus],
            'performed_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

	        return response()->json(['success' => true, 'status' => $newStatus]);
	    }



    /**
     * Search recipients within same clinic
     */
    public function recipients(Request $request)
    {
        $user = $request->user();
        $q = trim((string) $request->get('query', ''));
        $base = User::query()
            ->where('clinic_id', $user->clinic_id)
            ->where('is_active', true)
            ->where('id', '!=', $user->id);
        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('first_name', 'like', "%$q%")
                  ->orWhere('last_name', 'like', "%$q%")
                  ->orWhere('email', 'like', "%$q%")
                  ->orWhere('username', 'like', "%$q%");
            });
        }
        $users = $base->limit(20)->get(['id','first_name','last_name','role']);
        $out = $users->map(fn($u) => [
            'id' => $u->id,
            'name' => trim(($u->first_name.' '.$u->last_name)) ?: ('User #'.$u->id),
            'role' => $u->role,
        ]);
        return response()->json(['success' => true, 'recipients' => $out]);
    }


    /**
     * List messages in a conversation (latest 50), with sender and transfer info
     */
    public function conversationMessages(Request $request, Conversation $conversation)
    {
        $user = $request->user();
        if ($conversation->clinic_id !== $user->clinic_id) abort(403);
        if (! $conversation->participants()->where('user_id', $user->id)->exists()) abort(403);

        $messages = $conversation->messages()
            ->with(['sender:id,first_name,last_name,role', 'transfer.patient'])
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'type' => $m->message_type,
                    'body' => $m->body,
                    'created_at' => $m->created_at,
                    'sender' => [
                        'id' => $m->sender->id,
                        'name' => ($m->sender->first_name . ' ' . $m->sender->last_name),
                        'role' => $m->sender->role,
                    ],
                    'transfer' => $m->transfer ? [
                        'id' => $m->transfer->id,
                        'status' => $m->transfer->status,
                        'transfer_type' => $m->transfer->transfer_type,
                        'patient_id' => $m->transfer->patient_id,
                        'metadata' => $m->transfer->metadata,
                        'patient' => $m->transfer->patient ? [
                            'id' => $m->transfer->patient->id,
                            'full_name' => $m->transfer->patient->full_name ?? trim(($m->transfer->patient->first_name.' '.$m->transfer->patient->last_name)),
                        ] : null,
                    ] : null,
                ];
            });

        return response()->json(['success' => true, 'messages' => $messages]);
    }

}

