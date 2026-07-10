<?php

namespace App\Console\Commands;

use App\Domain\AppointmentReminders\Repositories\AppointmentReminderRepository;
use App\Jobs\SendAppointmentReminderJob;
use Illuminate\Console\Command;

class DispatchDueRemindersCommand extends Command
{
    protected $signature   = 'reminders:dispatch {--limit=100 : Max reminders per batch}';
    protected $description = 'Dispatch all pending appointment reminders that are due';

    public function handle(AppointmentReminderRepository $repository): int
    {
        $limit    = (int) $this->option('limit');
        $due      = $repository->getDueReminders($limit);
        $dispatched = 0;

        foreach ($due as $reminder) {
            SendAppointmentReminderJob::dispatch($reminder->id);
            $dispatched++;
        }

        $this->info("Dispatched {$dispatched} reminder(s).");
        return Command::SUCCESS;
    }
}