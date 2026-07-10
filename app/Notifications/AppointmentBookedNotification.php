<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentBookedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];  // ✅ bell + email
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'          => 'New Appointment Booked',
            'message'        => "Your appointment on {$this->appointment->start_time} is pending waiting for approval.",
            'appointment_id' => $this->appointment->id,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'calendar',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Appointment Confirmed')
            ->line("Your appointment on {$this->appointment->start_time} is confirmed.")
            ->action('View Appointment', url("/appointments/{$this->appointment->id}"));
    }
}