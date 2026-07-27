<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public int $recipientId;

    public function __construct(Message $message, int $recipientId)
    {
        $this->message = $message;
        $this->recipientId = $recipientId;
    }

    /**
     * One private channel per (clinic, user). Same channel pattern is reused
     * by other clinic-scoped per-user events going forward.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("clinic.{$this->message->clinic_id}.user.{$this->recipientId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.received';
    }

    /**
     * Lean payload — enough for the doctor's listener to:
     *   - refresh the unread badge
     *   - decide whether to render an urgent toast
     *   - know which patient the toast points to
     */
    public function broadcastWith(): array
    {
        $this->message->loadMissing(
            'sender:id,first_name,last_name,role',
            'patient:id,patient_id,first_name,last_name',
            'transfer'
        );

        $transfer = $this->message->transfer;
        $patient = $this->message->patient;
        $sender = $this->message->sender;

        return [
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'message_type' => $this->message->message_type,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $sender ? trim($sender->first_name . ' ' . $sender->last_name) : null,
            'sender_role' => $sender->role ?? null,
            'patient_id' => $this->message->patient_id,
            'patient_name' => $patient ? trim($patient->first_name . ' ' . $patient->last_name) : null,
            'patient_code' => $patient->patient_id ?? null,
            'patient_url' => $this->message->patient_id
                ? url('/patients/' . $this->message->patient_id)
                : null,
            'transfer' => $transfer ? [
                'id' => $transfer->id,
                'transfer_type' => $transfer->transfer_type,
                'priority' => $transfer->priority,
                'status' => $transfer->status,
                'note' => $transfer->metadata['note'] ?? null,
            ] : null,
            'created_at' => optional($this->message->created_at)->toIso8601String(),
        ];
    }
}
