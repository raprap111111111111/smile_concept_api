<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $when = Carbon::parse($this->appointment->start_time)
            ->format('l, F j, Y \a\t g:i A');

        return [
            'title'          => 'Appointment Rescheduled',
            'message'        => "The appointment has been moved to {$when}.",
            'appointment_id' => $this->appointment->id,
            'start_time'     => $this->appointment->start_time,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'calendar',
            'color'          => 'yellow',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $newTime = Carbon::parse($this->appointment->start_time)
            ->format('l, F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject('Your Appointment Has Been Rescheduled')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your appointment has been rescheduled.')
            ->line("**New Schedule:** {$newTime}")
            ->line("**Doctor:** " . ($this->appointment->doctor?->user?->name ?? 'N/A'))
            ->line("**Branch:** " . ($this->appointment->branch?->name ?? 'N/A'))
            ->action('View Appointment', url("/appointments/{$this->appointment->id}"))
            ->line('If you did not request this change, please contact us immediately.');
    }
}