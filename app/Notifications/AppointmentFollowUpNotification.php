<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Notifications\Concerns\NotifiesAppointmentPatient;
use App\Notifications\Contracts\RoutesAppointmentMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Aftercare / feedback email, sent by appointments:send-followups once an
 * appointment has been completed for `followup_email_hours_after` hours.
 * Gated by the `send_followup_email` setting.
 */
class AppointmentFollowUpNotification extends Notification implements ShouldQueue, RoutesAppointmentMail
{
    use Queueable, SerializesModels, NotifiesAppointmentPatient;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->patientChannels(
            $notifiable,
            settingKey: 'send_followup_email',
        );
    }

    public function toDatabase(object $notifiable): array
    {
        $when = $this->appointment->start_time?->format('l, F j, Y');

        return [
            'title'          => 'How was your visit?',
            'message'        => "We hope your appointment on {$when} went well. Tap for aftercare tips or to share feedback.",
            'appointment_id' => $this->appointment->id,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'heart',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('How was your visit to ' . setting('clinic_name', 'our clinic') . '?')
            ->markdown('emails.appointments.followup', $this->appointmentMailData());
    }
}
