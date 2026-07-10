<?php

namespace App\Notifications;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'          => 'Invoice Overdue',
            'message'        => "Invoice {$this->invoice->invoice_number} is {$this->getDaysOverdue()} day(s) overdue.",
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'balance_due'    => $this->invoice->balance_due,
            'days_overdue'   => $this->getDaysOverdue(),
            'action_url'     => "/invoices/{$this->invoice->id}",
            'icon'           => 'alert-triangle',
            'color'          => 'red',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysOverdue = $this->getDaysOverdue();
        $dueDate     = $this->getDueDateFormatted();

        return (new MailMessage)
            ->subject("Payment Overdue — Invoice {$this->invoice->invoice_number}")
            ->error()
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that invoice **{$this->invoice->invoice_number}** is now **{$daysOverdue} day(s) overdue**.")
            ->line("**Outstanding Balance:** ₱" . number_format((float) $this->invoice->balance_due, 2))
            ->line("**Original Due Date:** {$dueDate}")
            ->action('Pay Now', url("/invoices/{$this->invoice->id}/pay"))
            ->line('Please settle your balance at your earliest convenience to avoid late fees.')
            ->line('If you have already paid, please disregard this notice.');
    }

    /**
     * Get days overdue as a positive integer.
     * Returns 0 if no due date is set.
     */
    private function getDaysOverdue(): int
    {
        if (!$this->invoice->due_date) {
            return 0;
        }

        $dueDate = Carbon::parse($this->invoice->due_date);

        // ✅ Always positive & integer (prevents "-5 days overdue" bugs)
        return (int) abs($dueDate->diffInDays(now()));
    }

    /**
     * Get the formatted due date or 'N/A' fallback.
     */
    private function getDueDateFormatted(): string
    {
        if (!$this->invoice->due_date) {
            return 'N/A';
        }

        return Carbon::parse($this->invoice->due_date)->format('F j, Y');
    }
}