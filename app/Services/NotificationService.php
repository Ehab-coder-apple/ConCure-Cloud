<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Models\Patient;
use App\Models\PatientVaccination;
use App\Models\ScheduledNotification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected WhatsAppService $whatsApp;

    public function __construct(WhatsAppService $whatsApp)
    {
        $this->whatsApp = $whatsApp;
    }

    /**
     * Replace placeholders in a template with actual data.
     */
    public function parseTemplate(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{$key}}", (string) $value, $template);
        }
        return $template;
    }

    /**
     * Schedule a notification for future delivery.
     *
     * If a pending notification already exists for the same notifiable entity
     * and type (scoped by clinic_id), the old one is cancelled first.
     */
    public function schedule(
        int $clinicId,
        int $patientId,
        string $type,
        \DateTimeInterface $scheduledAt,
        ?string $notifiableType = null,
        ?int $notifiableId = null,
        array $payload = [],
        string $channel = 'whatsapp'
    ): ScheduledNotification {
        // Cancel any existing pending notification for the same entity + type + clinic
        if ($notifiableType && $notifiableId) {
            ScheduledNotification::withoutGlobalScopes()
                ->where('clinic_id', $clinicId)
                ->where('notifiable_type', $notifiableType)
                ->where('notifiable_id', $notifiableId)
                ->where('type', $type)
                ->where('status', ScheduledNotification::STATUS_PENDING)
                ->update(['status' => ScheduledNotification::STATUS_CANCELLED]);
        }

        return ScheduledNotification::withoutGlobalScopes()->create([
            'clinic_id' => $clinicId,
            'patient_id' => $patientId,
            'type' => $type,
            'channel' => $channel,
            'scheduled_at' => $scheduledAt,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'payload' => $payload,
        ]);
    }

    /**
     * Send a single scheduled notification.
     */
    public function send(ScheduledNotification $notification): bool
    {
        $notification->markProcessing();

        try {
            // Load clinic settings (bypass global scope — CLI context)
            $settings = NotificationSetting::withoutGlobalScopes()
                ->where('clinic_id', $notification->clinic_id)
                ->first();

            if (!$settings || !$settings->whatsapp_enabled) {
                $notification->cancel();
                Log::info("Notification #{$notification->id} cancelled: WhatsApp disabled for clinic #{$notification->clinic_id}");
                return false;
            }

            // Build message from template + payload
            $template = $settings->getEffectiveTemplate($notification->type);
            $message = $this->parseTemplate($template, $notification->payload ?? []);

            // Resolve recipient phone
            $patient = Patient::withoutGlobalScopes()->find($notification->patient_id);
            $phone = $patient?->whatsapp_phone ?: $patient?->phone;

            if (!$phone) {
                $notification->markFailed('Patient has no phone/WhatsApp number.');
                return false;
            }

            // Ensure WhatsApp service uses the correct clinic credentials (important for CLI/scheduler)
            $this->whatsApp->setClinicContext($notification->clinic_id);

            // Send via WhatsApp service
            $result = $this->whatsApp->sendMessage($phone, $message);

            // Log the attempt
            $log = NotificationLog::withoutGlobalScopes()->create([
                'clinic_id' => $notification->clinic_id,
                'patient_id' => $notification->patient_id,
                'type' => $notification->type,
                'channel' => $notification->channel,
                'recipient' => $phone,
                'message' => $message,
                'status' => ($result['success'] ?? false) ? NotificationLog::STATUS_SENT : NotificationLog::STATUS_FAILED,
                'error_message' => $result['error'] ?? null,
                'external_id' => $result['message_id'] ?? $result['message_sid'] ?? null,
                'notifiable_type' => $notification->notifiable_type,
                'notifiable_id' => $notification->notifiable_id,
                'sent_at' => ($result['success'] ?? false) ? now() : null,
            ]);

            if ($result['success'] ?? false) {
                $notification->markSent();
                Log::info("Notification #{$notification->id} sent to {$phone}");
                return true;
            } else {
                $notification->markFailed($result['error'] ?? 'Unknown send error');
                return false;
            }
        } catch (\Throwable $e) {
            Log::error("Notification #{$notification->id} error: {$e->getMessage()}");
            $notification->markFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Schedule an appointment reminder (called when appointment is created/updated).
     */
    public function scheduleAppointmentReminder(Appointment $appointment): ?ScheduledNotification
    {
        $settings = NotificationSetting::withoutGlobalScopes()
            ->where('clinic_id', $appointment->clinic_id)
            ->first();

        if (!$settings || !$settings->appointment_reminder_enabled || !$settings->whatsapp_enabled) {
            return null;
        }

        // Don't schedule for past appointments or cancelled/completed ones
        if ($appointment->appointment_datetime->isPast() || in_array($appointment->status, ['cancelled', 'completed', 'no_show'])) {
            return null;
        }

        $sendAt = $appointment->appointment_datetime->subHours($settings->appointment_reminder_hours);

        // Don't schedule if the send time is already past
        if ($sendAt->isPast()) {
            return null;
        }

        $clinic = Clinic::find($appointment->clinic_id);
        $patient = $appointment->patient;
        $doctor = $appointment->doctor;

        $type = $appointment->type === 'follow_up' ? 'follow_up_reminder' : 'appointment_reminder';

        return $this->schedule(
            clinicId: $appointment->clinic_id,
            patientId: $appointment->patient_id,
            type: $type,
            scheduledAt: $sendAt,
            notifiableType: Appointment::class,
            notifiableId: $appointment->id,
            payload: $this->buildAppointmentPayload($patient, $appointment, $doctor, $clinic),
        );
    }

    /**
     * Schedule a vaccination reminder.
     */
    public function scheduleVaccinationReminder(PatientVaccination $vaccination): ?ScheduledNotification
    {
        $settings = NotificationSetting::withoutGlobalScopes()
            ->where('clinic_id', $vaccination->patient?->clinic_id)
            ->first();

        $clinicId = $vaccination->patient?->clinic_id;
        if (!$clinicId || !$settings || !$settings->vaccination_reminder_enabled || !$settings->whatsapp_enabled) {
            return null;
        }

        $dueDate = $vaccination->scheduled_date;
        if (!$dueDate || $dueDate->isPast()) {
            return null;
        }

        $sendAt = $dueDate->copy()->subDays($settings->vaccination_reminder_days);
        if ($sendAt->isPast()) {
            return null;
        }

        $clinic = Clinic::find($clinicId);
        $patient = $vaccination->patient;

        return $this->schedule(
            clinicId: $clinicId,
            patientId: $patient->id,
            type: 'vaccination_reminder',
            scheduledAt: $sendAt,
            notifiableType: PatientVaccination::class,
            notifiableId: $vaccination->id,
            payload: $this->buildVaccinationPayload($patient, $vaccination, $clinic),
        );
    }

    /**
     * Immediately send a manual reminder for a specific entity.
     *
     * Bypasses the schedule queue — creates a ScheduledNotification with
     * scheduled_at = now(), sends it immediately, and records the result
     * in notification_logs.
     *
     * @param  string  $type         One of: appointment_reminder, follow_up_reminder, vaccination_reminder
     * @param  int     $referenceId  The ID of the Appointment or PatientVaccination record
     * @return bool    Whether the send succeeded
     */
    public function sendManualReminder(string $type, int $referenceId): bool
    {
        // Resolve the notifiable entity and build a scheduled notification on the fly
        if (in_array($type, ['appointment_reminder', 'follow_up_reminder'])) {
            $appointment = Appointment::withoutGlobalScopes()->find($referenceId);
            if (!$appointment) {
                Log::warning("sendManualReminder: Appointment #{$referenceId} not found.");
                return false;
            }

            $clinicId = $appointment->clinic_id;
            $patient = Patient::withoutGlobalScopes()->find($appointment->patient_id);
            $doctor = $appointment->doctor;
            $clinic = Clinic::find($clinicId);

            $settings = NotificationSetting::withoutGlobalScopes()
                ->where('clinic_id', $clinicId)
                ->first();

            if (!$settings || !$settings->whatsapp_enabled) {
                Log::info("sendManualReminder: WhatsApp disabled for clinic #{$clinicId}");
                return false;
            }

            $payload = $this->buildAppointmentPayload($patient, $appointment, $doctor, $clinic);

            $notification = ScheduledNotification::withoutGlobalScopes()->create([
                'clinic_id' => $clinicId,
                'patient_id' => $appointment->patient_id,
                'type' => $type,
                'channel' => 'whatsapp',
                'scheduled_at' => now(),
                'notifiable_type' => Appointment::class,
                'notifiable_id' => $appointment->id,
                'payload' => $payload,
            ]);

            return $this->send($notification);

        } elseif ($type === 'vaccination_reminder') {
            $vaccination = PatientVaccination::withoutGlobalScopes()->with('patient', 'vaccine')->find($referenceId);
            if (!$vaccination || !$vaccination->patient) {
                Log::warning("sendManualReminder: PatientVaccination #{$referenceId} not found.");
                return false;
            }

            $clinicId = $vaccination->patient->clinic_id;
            $clinic = Clinic::find($clinicId);

            $settings = NotificationSetting::withoutGlobalScopes()
                ->where('clinic_id', $clinicId)
                ->first();

            if (!$settings || !$settings->whatsapp_enabled) {
                Log::info("sendManualReminder: WhatsApp disabled for clinic #{$clinicId}");
                return false;
            }

            $payload = $this->buildVaccinationPayload($vaccination->patient, $vaccination, $clinic);

            $notification = ScheduledNotification::withoutGlobalScopes()->create([
                'clinic_id' => $clinicId,
                'patient_id' => $vaccination->patient->id,
                'type' => 'vaccination_reminder',
                'channel' => 'whatsapp',
                'scheduled_at' => now(),
                'notifiable_type' => PatientVaccination::class,
                'notifiable_id' => $vaccination->id,
                'payload' => $payload,
            ]);

            return $this->send($notification);
        }

        Log::warning("sendManualReminder: Unknown notification type '{$type}'.");
        return false;
    }

    /**
     * Build common patient data array used in all payload types.
     */
    protected function buildPatientData(?Patient $patient): array
    {
        if (!$patient) {
            return [
                'patient_name' => '',
                'patient_first_name' => '',
                'patient_last_name' => '',
                'patient_id' => '',
                'patient_phone' => '',
                'patient_age' => '',
                'patient_gender' => '',
                'patient_dob' => '',
            ];
        }

        return [
            'patient_name' => $patient->full_name ?? '',
            'patient_first_name' => $patient->first_name ?? '',
            'patient_last_name' => $patient->last_name ?? '',
            'patient_id' => $patient->patient_id ?? '',
            'patient_phone' => $patient->whatsapp_phone ?: ($patient->phone ?? ''),
            'patient_age' => (string) ($patient->age ?? ''),
            'patient_gender' => $patient->gender ?? '',
            'patient_dob' => $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '',
        ];
    }

    /**
     * Build payload for appointment-based notifications.
     */
    protected function buildAppointmentPayload(?Patient $patient, Appointment $appointment, $doctor, ?Clinic $clinic): array
    {
        return array_merge($this->buildPatientData($patient), [
            'appointment_date' => $appointment->appointment_datetime->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_datetime->format('H:i'),
            'doctor_name' => trim(($doctor?->first_name ?? '') . ' ' . ($doctor?->last_name ?? '')),
            'clinic_name' => $clinic?->name ?? '',
            'clinic_phone' => $clinic?->phone ?? '',
            'clinic_address' => $clinic?->formatted_address ?? '',
            'appointment_type' => Appointment::TYPES[$appointment->type] ?? $appointment->type,
        ]);
    }

    /**
     * Build payload for vaccination-based notifications.
     */
    protected function buildVaccinationPayload(?Patient $patient, PatientVaccination $vaccination, ?Clinic $clinic): array
    {
        $dueDate = $vaccination->scheduled_date;

        return array_merge($this->buildPatientData($patient), [
            'vaccine_name' => $vaccination->vaccine?->name ?? 'Vaccination',
            'due_date' => $dueDate ? $dueDate->format('Y-m-d') : '',
            'clinic_name' => $clinic?->name ?? '',
            'clinic_phone' => $clinic?->phone ?? '',
            'clinic_address' => $clinic?->formatted_address ?? '',
        ]);
    }
}

