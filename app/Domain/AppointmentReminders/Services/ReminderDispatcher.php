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

        // Calculate hours until appointment
        $hoursBefore = (int) Carbon::parse($appointment->start_time)
            ->diffInHours(now());

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
     * The notification's via() method returns ['database', 'mail'].
     */
    private function sendEmail(
        AppointmentReminder $reminder,
        $patient,
        $appointment,
        int $hoursBefore
    ): void {
        if (!$patient->email) {
            throw new \RuntimeException('Patient has no email address.');
        }

        // ✅ Uses your AppointmentReminderNotification
        $patient->notify(
            new AppointmentReminderNotification($appointment, $hoursBefore)
        );

        Log::info("Email reminder sent", [
            'reminder_id'    => $reminder->id,
            'appointment_id' => $appointment->id,
            'email'          => $patient->email,
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
            new AppointmentReminderNotification($appointment, $hoursBefore)
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
            new AppointmentReminderNotification($appointment, $hoursBefore)
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
            new AppointmentReminderNotification($appointment, $hoursBefore)
        );

        Log::info("In-app reminder created", ['reminder_id' => $reminder->id]);
    }
}