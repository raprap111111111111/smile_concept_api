<?php

namespace App\Domain\AppointmentReminders\Actions;

use App\Domain\AppointmentReminders\Repositories\AppointmentReminderRepository;
use App\Domain\AppointmentReminders\Services\ReminderScheduler;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;

class ScheduleReminderAction
{
    public function __construct(
        private readonly AppointmentReminderRepository $repository,
        private readonly ReminderScheduler             $scheduler,
    ) {}

    /**
     * Auto-generate all reminder entries when an appointment is booked.
     *
     * @return int Number of reminders created
     */
    public function execute(Appointment $appointment): int
    {
        $schedule = $this->scheduler->computeSchedule($appointment);

        if (empty($schedule)) {
            return 0;
        }

        return DB::transaction(function () use ($appointment, $schedule) {
            $count = 0;

            foreach ($schedule as $entry) {
                $this->repository->create([
                    'appointment_id' => $appointment->id,
                    'channel'        => $entry['channel'],
                    'status'         => 'pending',
                    'scheduled_for'  => $entry['scheduled_for'],
                ]);
                $count++;
            }

            return $count;
        });
    }
}