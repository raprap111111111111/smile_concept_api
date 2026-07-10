<?php

namespace App\Jobs;

use App\Domain\AppointmentReminders\Actions\SendReminderAction;
use App\Models\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly int $reminderId,
    ) {}

    public function handle(SendReminderAction $action): void
    {
        $reminder = AppointmentReminder::with('appointment.user')->find($this->reminderId);

        if (!$reminder) {
            return;
        }

        $action->execute($reminder);
    }
}