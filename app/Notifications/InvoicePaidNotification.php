<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaidNotification extends Notification implements ShouldQueue
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
            'title'          => 'Invoice Paid',
            'message'        => "Invoice {$this->invoice->invoice_number} has been fully paid.",
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'total_amount'   => $this->invoice->total_amount,
            'action_url'     => "/invoices/{$this->invoice->id}",
            'icon'           => 'check-circle',
            'color'          => 'green',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment Received — Invoice {$this->invoice->invoice_number}")
            ->greeting("Thank you, {$notifiable->name}!")
            ->line("We have received your payment for invoice **{$this->invoice->invoice_number}**.")
            ->line("**Total Amount:** ₱" . number_format((float) $this->invoice->total_amount, 2))
            ->line("**Status:** Fully Paid ✅")
            ->action('View Receipt', url("/invoices/{$this->invoice->id}"))
            ->line('Thank you for choosing Smile Concept Dental!');
    }
}