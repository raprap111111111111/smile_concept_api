<?php

namespace App\Jobs;

use App\Domain\AppointmentReminders\Actions\SendReminderAction;
use App\Models\AppointmentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ShouldBeUnique is load-bearing, not a nicety.
 *
 * DispatchDueRemindersCommand selects rows with status 'pending' and dispatches
 * them without claiming the row, and the scheduler re-runs it every five
 * minutes. On the sync driver the send finished inline, so the row was already
 * 'sent' by the next run. On a real queue the row stays 'pending' until a
 * worker picks it up -- so a stopped worker means the same reminder is queued
 * again every five minutes, and the whole backlog is delivered at once when the
 * worker restarts. The lock closes that window.
 */
class SendAppointmentReminderJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;

    /** Lock lifetime, comfortably longer than tries x backoff. */
    public int $uniqueFor = 900;

    public function __construct(
        public readonly int $reminderId,
    ) {}

    public function uniqueId(): string
    {
        return (string) $this->reminderId;
    }

    public function handle(SendReminderAction $action): void
    {
        $reminder = AppointmentReminder::with('appointment.user')->find($this->reminderId);

        if (!$reminder) {
            return;
        }

        $action->execute($reminder);
    }
}