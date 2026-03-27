<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationSettingsController extends Controller
{
    /**
     * Show the notification settings page (clinic admins only).
     */
    public function index()
    {
        $user = auth()->user();
        $clinicId = $user->clinic_id;

        if (!$clinicId || (!$user->isSuperAdmin() && !$user->isClinicAdmin())) {
            abort(403, __('Only clinic administrators can manage notification settings.'));
        }

        $settings = NotificationSetting::forClinic($clinicId);

        // Recent notification logs for the activity feed
        $recentLogs = NotificationLog::where('clinic_id', $clinicId)
            ->with('patient')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Stats
        $stats = [
            'total_sent' => NotificationLog::where('clinic_id', $clinicId)->where('status', 'sent')->count(),
            'total_failed' => NotificationLog::where('clinic_id', $clinicId)->where('status', 'failed')->count(),
            'last_7_days' => NotificationLog::where('clinic_id', $clinicId)->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('settings.notifications.index', compact('settings', 'recentLogs', 'stats'));
    }

    /**
     * Update notification settings (clinic admins only).
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $clinicId = $user->clinic_id;

        if (!$clinicId || (!$user->isSuperAdmin() && !$user->isClinicAdmin())) {
            abort(403, __('Only clinic administrators can manage notification settings.'));
        }

        $validated = $request->validate([
            'whatsapp_enabled' => 'nullable|boolean',
            'appointment_reminder_enabled' => 'nullable|boolean',
            'appointment_reminder_hours' => 'required_if:appointment_reminder_enabled,1|integer|min:1|max:168',
            'appointment_reminder_template' => 'nullable|string|max:1000',
            'vaccination_reminder_enabled' => 'nullable|boolean',
            'vaccination_reminder_days' => 'required_if:vaccination_reminder_enabled,1|integer|min:1|max:30',
            'vaccination_reminder_template' => 'nullable|string|max:1000',
            'follow_up_reminder_enabled' => 'nullable|boolean',
            'follow_up_reminder_days' => 'required_if:follow_up_reminder_enabled,1|integer|min:1|max:30',
            'follow_up_reminder_template' => 'nullable|string|max:1000',
        ]);

        $settings = NotificationSetting::forClinic($clinicId);

        $settings->update([
            'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
            'appointment_reminder_enabled' => $request->boolean('appointment_reminder_enabled'),
            'appointment_reminder_hours' => $validated['appointment_reminder_hours'] ?? 24,
            'appointment_reminder_template' => $validated['appointment_reminder_template'] ?: null,
            'vaccination_reminder_enabled' => $request->boolean('vaccination_reminder_enabled'),
            'vaccination_reminder_days' => $validated['vaccination_reminder_days'] ?? 3,
            'vaccination_reminder_template' => $validated['vaccination_reminder_template'] ?: null,
            'follow_up_reminder_enabled' => $request->boolean('follow_up_reminder_enabled'),
            'follow_up_reminder_days' => $validated['follow_up_reminder_days'] ?? 1,
            'follow_up_reminder_template' => $validated['follow_up_reminder_template'] ?: null,
        ]);

        return redirect()->route('notifications.settings')
            ->with('success', __('Notification settings updated successfully.'));
    }

    /**
     * Send a manual reminder (AJAX).
     */
    public function sendReminder(Request $request, NotificationService $service): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:appointment_reminder,follow_up_reminder,vaccination_reminder',
            'reference_id' => 'required|integer',
        ]);

        try {
            $success = $service->sendManualReminder($request->type, $request->reference_id);

            return response()->json([
                'success' => $success,
                'message' => $success
                    ? __('Reminder sent successfully.')
                    : __('Failed to send reminder. Check notification settings.'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error: ') . $e->getMessage(),
            ], 500);
        }
    }
}

