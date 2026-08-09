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

class AppointmentRescheduledNotification extends Notification implements ShouldQueue, RoutesAppointmentMail
{
    use Queueable, SerializesModels, NotifiesAppointmentPatient;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->patientChannels($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        $when = $this->appointment->start_time?->format('l, F j, Y \a\t g:i A');

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
        return (new MailMessage)
            ->subject('Your appointment has been rescheduled')
            ->markdown('emails.appointments.rescheduled', $this->appointmentMailData());
    }
}
