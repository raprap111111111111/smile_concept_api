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
        if ($reminder->status !== 'pending') {
            return false;
        }

        try {
            $this->dispatcher->dispatch($reminder);
            $reminder->markAsSent();
            return true;
        } catch (\Throwable $e) {
            $reminder->markAsFailed($e->getMessage());
            return false;
        }
    }
}