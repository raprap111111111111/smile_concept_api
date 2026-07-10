<?php

namespace App\Enums;

enum TreatmentPlanStatus: string
{
    case DRAFT = 'draft';
    case PROPOSED = 'proposed';
    case ACCEPTED = 'accepted';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PROPOSED => 'Proposed Estimate',
            self::ACCEPTED => 'Accepted / Approved',
            self::COMPLETED => 'Treatment Completed',
            self::REJECTED => 'Rejected',
        };
    }
}
