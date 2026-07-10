<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly ?string     $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'          => 'Appointment Cancelled',
            'message'        => "Your appointment on {$this->appointment->start_time} has been cancelled.",
            'reason'         => $this->reason,
            'appointment_id' => $this->appointment->id,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'x-circle',
            'color'          => 'red',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Appointment Cancelled')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your appointment scheduled for {$this->appointment->start_time} has been cancelled.");

        if ($this->reason) {
            $mail->line("Reason: {$this->reason}");
        }

        return $mail
            ->action('Book a New Appointment', url('/book'))
            ->line('We hope to see you soon!');
    }
}