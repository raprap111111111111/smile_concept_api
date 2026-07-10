<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPatientRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $patient,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'        => 'New Patient Registered',
            'message'      => "{$this->patient->name} has just registered as a new patient.",
            'patient_id'   => $this->patient->id,
            'patient_name' => $this->patient->name,
            'patient_email'=> $this->patient->email,
            'action_url'   => "/patients/{$this->patient->id}",
            'icon'         => 'user-plus',
            'color'        => 'green',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Patient Registered')
            ->greeting("Hello {$notifiable->name},")
            ->line("A new patient has just registered in the system:")
            ->line("**Name:** {$this->patient->name}")
            ->line("**Email:** {$this->patient->email}")
            ->line("**Phone:** " . ($this->patient->phone ?? 'Not provided'))
            ->action('View Patient Profile', url("/patients/{$this->patient->id}"))
            ->line('Please review and prepare for their first visit.');
    }
}