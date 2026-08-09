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
 * Sent twice in an appointment's life:
 *   - on creation, while the booking is still PENDING approval,
 *   - again when staff move it to CONFIRMED.
 *
 * $confirmed decides which of the two this is. Without it the patient would be
 * told "confirmed" for a request nobody has approved yet.
 */
class AppointmentBookedNotification extends Notification implements ShouldQueue, RoutesAppointmentMail
{
    use Queueable, SerializesModels, NotifiesAppointmentPatient;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly bool        $confirmed = false,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->patientChannels($notifiable);
    }

    public function toDatabase(object $notifiable): array
    {
        $when = $this->appointment->start_time?->format('l, F j, Y \a\t g:i A');

        return [
            'title'          => $this->confirmed ? 'Appointment Confirmed' : 'New Appointment Booked',
            'message'        => $this->confirmed
                ? "Your appointment on {$when} is confirmed."
                : "Your appointment on {$when} is pending waiting for approval.",
            'appointment_id' => $this->appointment->id,
            'action_url'     => "/appointments/{$this->appointment->id}",
            'icon'           => 'calendar',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->confirmed
                ? 'Your appointment is confirmed'
                : 'We received your appointment request')
            ->markdown('emails.appointments.booked', $this->appointmentMailData([
                'confirmed' => $this->confirmed,
            ]));
    }
}
