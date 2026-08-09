<?php

namespace App\Domain\AppointmentReminders\Actions;

use App\Domain\AppointmentReminders\Services\ReminderDispatcher;
use App\Models\AppointmentReminder;

class SendReminderAction
{
    public function __construct(
        private readonly ReminderDispatcher $dispatcher,
    ) {}

    public function execute(AppointmentReminder $reminder): bool
    {
        // 'failed' is allowed through so a retried job is not rejected by the
        // failure marker its own previous attempt wrote.
        if (!in_array($reminder->status, ['pending', 'failed'], true)) {
            return false;
        }

        try {
            $this->dispatcher->dispatch($reminder);
            $reminder->markAsSent();
            return true;
        } catch (\Throwable $e) {
            // Record the failure, then rethrow. Swallowing it here made the
            // job report success, so SendAppointmentReminderJob::$tries never
            // retried and a transient SMTP blip killed the reminder for good.
            $reminder->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}