<?php

namespace App\Domain\AppointmentReminders\Services;

use App\Models\AppointmentReminder;
use App\Notifications\AppointmentReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderDispatcher
{
    /**
     * Route a reminder to the correct channel handler.
     */
    public function dispatch(AppointmentReminder $reminder): void
    {
        $appointment = $reminder->appointment;
        $patient     = $appointment?->user;

        if (!$appointment) {
            throw new \RuntimeException("Reminder has no associated appointment.");
        }

        if (!$patient) {
            throw new \RuntimeException("Appointment has no associated user.");
        }

        // How far ahead this reminder was meant to land. Derived from the
        // reminder row rather than now(): the scheduler runs every five
        // minutes, so measuring against the clock turns a 24h reminder into
        // 23h. Carbon 3 also returns a signed float, and the receiver/argument
        // order here used to be inverted, which made every value negative.
        $hoursBefore = (int) round(
            Carbon::parse($reminder->scheduled_for)->diffInHours($appointment->start_time)
        );

        match ($reminder->channel) {
            'email'  => $this->sendEmail($reminder, $patient, $appointment, $hoursBefore),
            'sms'    => $this->sendSms($reminder, $patient, $appointment, $hoursBefore),
            'push'   => $this->sendPush($reminder, $patient, $appointment, $hoursBefore),
            'in_app' => $this->sendInApp($reminder, $patient, $appointment, $hoursBefore),
            default  => throw new \InvalidArgumentException(
                "Unsupported reminder channel: {$reminder->channel}"
            ),
        };
    }

    /**
     * Send via email + database (bell).
     *
     * The 'email' third argument is what puts 'mail' into the notification's
     * via(). Every channel below funnels through the same notification class,
     * so only this one may pass it -- otherwise a single appointment emails
     * once per reminder row.
     */
    private function sendEmail(
        AppointmentReminder $reminder,
        $patient,
        $appointment,
        int $hoursBefore
    ): void {
        // Checks the resolved address, not $patient->email: a booking made for
        // a spouse carries its own patient_email and should still go out even
        // if the account holder has none.
        if ($appointment->notificationEmail() === null) {
            throw new \RuntimeException('Appointment has no email address to notify.');
        }

        $patient->notify(
            new AppointmentReminderNotification($appointment, $hoursBefore, 'email')
        );

        Log::info("Email reminder sent", [
            'reminder_id'    => $reminder->id,
            'appointment_id' => $appointment->id,
            'email'          => $appointment->notificationEmail(),
        ]);
    }

    /**
     * Send via SMS gateway (Twilio, Semaphore, etc.).
     */
    private function sendSms(
        AppointmentReminder $reminder,
        $patient,
        $appointment,
        int $hoursBefore
    ): void {
        if (!$patient->phone) {
            throw new \RuntimeException('Patient has no phone number.');
        }

        // TODO: Integrate real SMS provider (Twilio/Semaphore)
        // For now, notify creates a bell entry which is fine
        $patient->notify(
            new AppointmentReminderNotification($appointment, $hoursBefore, 'sms')
        );

        Log::info("SMS reminder sent", [
            'reminder_id' => $reminder->id,
            'phone'       => $patient->phone,
        ]);
    }

    /**
     * Send via mobile push notification (FCM/APNs).
     */
    private function sendPush(
        AppointmentReminder $reminder,
        $patient,
        $appointment,
        int $hoursBefore
    ): void {
        // TODO: Integrate FCM/APNs
        $patient->notify(
            new AppointmentReminderNotification($appointment, $hoursBefore, 'push')
        );

        Log::info("Push reminder sent", ['reminder_id' => $reminder->id]);
    }

    /**
     * Send only as in-app (database/bell) notification.
     */
    private function sendInApp(
        AppointmentReminder $reminder,
        $patient,
        $appointment,
        int $hoursBefore
    ): void {
        $patient->notify(
            new AppointmentReminderNotification($appointment, $hoursBefore, 'in_app')
        );

        Log::info("In-app reminder created", ['reminder_id' => $reminder->id]);
    }
}