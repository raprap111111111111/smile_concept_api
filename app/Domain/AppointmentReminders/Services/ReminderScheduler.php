<?php

namespace App\Domain\AppointmentReminders\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class ReminderScheduler
{
    /**
     * Default reminder offsets (in hours before appointment).
     */
    private const OFFSETS = [24, 1];

    /**
     * Default channels to send reminders through.
     */
    private const CHANNELS = ['email', 'sms'];

    /**
     * Compute scheduled reminder times for an appointment.
     *
     * @return array<array{scheduled_for: string, channel: string}>
     */
    public function computeSchedule(Appointment $appointment): array
    {
        $schedule = [];
        $start    = Carbon::parse($appointment->start_time);

        foreach (self::OFFSETS as $hours) {
            $scheduledFor = $start->copy()->subHours($hours);

            // Skip reminders that would land in the past
            if ($scheduledFor->isPast()) {
                continue;
            }

            foreach (self::CHANNELS as $channel) {
                $schedule[] = [
                    'scheduled_for' => $scheduledFor->toDateTimeString(),
                    'channel'       => $channel,
                ];
            }
        }

        return $schedule;
    }
}