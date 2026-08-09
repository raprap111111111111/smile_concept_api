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

class AppointmentCancelledNotification extends Notification implements ShouldQueue, RoutesAppointmentMail
{
    use Queueable, SerializesModels, NotifiesAppointmentPatient;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly ?string     $reason = null,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->patientChannels($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        $when = $this->appointment->start_time?->format('l, F j, Y \a\t g:i A');

        $message = "The appointment on {$when} has been cancelled.";

        if ($this->reason) {
            $message .= " Reason: {$this->reason}";
        }

        return [
            'title'          => 'Appointment Cancelled',
            'message'        => $message,
            'reason'         => $this->reason,
            'appointment_id' => $this->appointment->id,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'x-circle',
            'color'          => 'red',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your appointment has been cancelled')
            ->markdown('emails.appointments.cancelled', $this->appointmentMailData([
                'reason' => $this->reason,
            ]));
    }
}
