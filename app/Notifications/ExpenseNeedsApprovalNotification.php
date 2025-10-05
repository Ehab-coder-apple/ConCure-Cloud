<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Expense;
use App\Models\User;

class ExpenseNeedsApprovalNotification extends Notification
{
    use Queueable;

    protected Expense $expense;
    protected User $submittedBy;
    protected bool $isUpdate;

    /**
     * Create a new notification instance.
     */
    public function __construct(Expense $expense, User $submittedBy, bool $isUpdate = false)
    {
        $this->expense = $expense;
        $this->submittedBy = $submittedBy;
        $this->isUpdate = $isUpdate;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for database channel.
     */
    public function toDatabase(object $notifiable): array
    {
        $action = $this->isUpdate ? 'updated' : 'created';
        $message = sprintf(
            'Expense #%s has been %s by %s and needs approval. Amount: $%s',
            $this->expense->expense_number,
            $action,
            $this->submittedBy->full_name,
            number_format($this->expense->amount, 2)
        );

        return [
            'type' => 'expense.needs_approval',
            'expense_id' => $this->expense->id,
            'expense_number' => $this->expense->expense_number,
            'amount' => $this->expense->amount,
            'description' => $this->expense->description,
            'submitted_by' => $this->submittedBy->full_name,
            'submitted_by_id' => $this->submittedBy->id,
            'clinic_id' => $this->expense->clinic_id,
            'is_update' => $this->isUpdate,
            'message' => $message,
        ];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $action = $this->isUpdate ? 'updated' : 'submitted';
        
        return (new MailMessage)
                    ->subject('Expense Approval Required')
                    ->line("An expense has been {$action} and requires your approval.")
                    ->line("Expense Number: {$this->expense->expense_number}")
                    ->line("Description: {$this->expense->description}")
                    ->line("Amount: $" . number_format($this->expense->amount, 2))
                    ->line("Submitted by: {$this->submittedBy->full_name}")
                    ->action('Review Expense', url('/finance/expenses'))
                    ->line('Please review and approve or reject this expense.');
    }
}
