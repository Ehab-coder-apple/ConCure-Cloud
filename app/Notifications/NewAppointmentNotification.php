<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewAppointmentNotification extends Notification
{
    use Queueable;

    protected int $appointmentId;
    protected string $appointmentNumber;
    protected ?string $patientName;
    protected ?string $patientCode;
    protected string $scheduledAt; // formatted string
    protected ?int $clinicId;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        int $appointmentId,
        string $appointmentNumber,
        ?string $patientName,
        ?string $patientCode,
        string $scheduledAt,
        ?int $clinicId = null
    ) {
        $this->appointmentId = $appointmentId;
        $this->appointmentNumber = $appointmentNumber;
        $this->patientName = $patientName;
        $this->patientCode = $patientCode;
        $this->scheduledAt = $scheduledAt;
        $this->clinicId = $clinicId;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        // Database notifications to show in-app; add 'mail' later if needed
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database channel.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'appointment.created',
            'appointment_id' => $this->appointmentId,
            'appointment_number' => $this->appointmentNumber,
            'patient_name' => $this->patientName,
            'patient_code' => $this->patientCode,
            'scheduled_at' => $this->scheduledAt,
            'clinic_id' => $this->clinicId,
            'message' => sprintf('New appointment #%s scheduled%s%s',
                $this->appointmentNumber,
                $this->patientName ? ' for ' . $this->patientName : '',
                $this->scheduledAt ? ' at ' . $this->scheduledAt : ''
            ),
        ];
    }

    /**
     * For compatibility with notifications UI that uses toArray()
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

