<?php

namespace App\Notifications;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly int         $hoursBefore = 24,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $when = Carbon::parse($this->appointment->start_time)->diffForHumans();

        return [
            'title'          => 'Appointment Reminder',
            'message'        => "Reminder: You have an appointment {$when}.",
            'appointment_id' => $this->appointment->id,
            'start_time'     => $this->appointment->start_time,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'bell',
            'color'          => 'blue',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $startTime = Carbon::parse($this->appointment->start_time)->format('l, F j, Y \a\t g:i A');

        return (new MailMessage)
            ->subject("Appointment Reminder — {$this->hoursBefore}h Away")
            ->greeting("Hi {$notifiable->name},")
            ->line("This is a friendly reminder that you have a scheduled appointment.")
            ->line("**When:** {$startTime}")
            ->line("**Doctor:** " . ($this->appointment->doctor?->user?->name ?? 'N/A'))
            ->line("**Branch:** " . ($this->appointment->branch?->name ?? 'N/A'))
            ->action('View Appointment', url("/appointments/{$this->appointment->id}"))
            ->line('Please arrive 10 minutes early. If you need to reschedule, contact us as soon as possible.');
    }
}