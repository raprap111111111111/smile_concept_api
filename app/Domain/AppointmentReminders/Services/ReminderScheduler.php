<?php

namespace App\Domain\AppointmentReminders\Services;

use App\Domain\Settings\DTOs\AppointmentSettings;
use App\Models\Appointment;
use Carbon\Carbon;

class ReminderScheduler
{
    /**
     * Offsets (hours before start) and channels come from the settings table:
     * `reminder_offsets` = [first, second], `reminder_channels`. Defaults live
     * in AppointmentSettings, so missing rows never break scheduling.
     */
    public function __construct(
        private readonly AppointmentSettings $settings,
    ) {}

    /**
     * Compute scheduled reminder times for an appointment.
     *
     * @return array<array{scheduled_for: string, channel: string}>
     */
    public function computeSchedule(Appointment $appointment): array
    {
        $schedule = [];
        $start    = Carbon::parse($appointment->start_time);

        foreach ($this->settings->reminderOffsets as $hours) {
            $scheduledFor = $start->copy()->subHours($hours);

            // Skip reminders that would land in the past
            if ($scheduledFor->isPast()) {
                continue;
            }

            foreach ($this->settings->reminderChannels as $channel) {
                $schedule[] = [
                    'scheduled_for' => $scheduledFor->toDateTimeString(),
                    'channel'       => $channel,
                ];
            }
        }

        return $schedule;
    }
}