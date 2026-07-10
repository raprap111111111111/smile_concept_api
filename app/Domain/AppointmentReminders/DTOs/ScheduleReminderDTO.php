<?php

namespace App\Domain\AppointmentReminders\DTOs;

final readonly class ScheduleReminderDTO
{
    public function __construct(
        public int    $appointmentId,
        public string $channel,          // sms | email | push | in_app
        public string $scheduledFor,     // Y-m-d H:i:s
    ) {}
}