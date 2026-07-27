<?php

namespace App\Notifications;

use App\Models\Receipt;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptNeedsApprovalNotification extends Notification
{
    use Queueable;

    public $receipt;
    public $submittedBy;
    public $isUpdate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Receipt $receipt, User $submittedBy, bool $isUpdate = false)
    {
        $this->receipt = $receipt;
        $this->submittedBy = $submittedBy;
        $this->isUpdate = $isUpdate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'receipt_approval',
            'receipt_id' => $this->receipt->id,
            'receipt_number' => $this->receipt->receipt_number,
            'amount' => $this->receipt->amount,
            'description' => $this->receipt->description,
            'category' => $this->receipt->category_name,
            'submitted_by' => $this->submittedBy->name,
            'submitted_by_id' => $this->submittedBy->id,
            'is_update' => $this->isUpdate,
            'message' => $this->isUpdate
                ? "Receipt {$this->receipt->receipt_number} has been updated and needs re-approval"
                : "New receipt {$this->receipt->receipt_number} needs approval",
            'action_url' => route('finance.receipts'),
        ];
    }
}
