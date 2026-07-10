<?php

namespace App\Enums;

enum RecallStatus: string
{
    case PENDING = 'pending';
    case NOTIFIED = 'notified';
    case SCHEDULED = 'scheduled';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Recall',
            self::NOTIFIED => 'Patient Notified',
            self::SCHEDULED => 'Appointment Booked',
            self::OVERDUE => 'Overdue Follow-up',
        };
    }
}
