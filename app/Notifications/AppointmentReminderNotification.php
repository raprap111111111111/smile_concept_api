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

class AppointmentReminderNotification extends Notification implements ShouldQueue, RoutesAppointmentMail
{
    use Queueable, SerializesModels, NotifiesAppointmentPatient;

    /**
     * @param  string  $reminderChannel  Which reminder row triggered this --
     *         'email', 'sms', 'push' or 'in_app'. ReminderScheduler creates one
     *         row per channel per offset, and every channel funnels through
     *         this same notification, so without it a single appointment would
     *         send an email for each row. Defaults to in_app so an unmigrated
     *         caller fails closed (bell only), never with a surprise email.
     */
    public function __construct(
        public readonly Appointment $appointment,
        public readonly int         $hoursBefore = 24,
        public readonly string      $reminderChannel = 'in_app',
    ) {}

    public function via(object $notifiable): array
    {
        return $this->patientChannels($notifiable, wantsMail: $this->reminderChannel === 'email');
    }

    public function toDatabase(object $notifiable): array
    {
        $when = $this->appointment->start_time?->diffForHumans();

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
        return (new MailMessage)
            ->subject($this->hoursBefore <= 2
                ? 'Your appointment is coming up shortly'
                : "Reminder: your appointment is in {$this->hoursBefore} hours")
            ->markdown('emails.appointments.reminder', $this->appointmentMailData([
                'hoursBefore' => $this->hoursBefore,
            ]));
    }
}
