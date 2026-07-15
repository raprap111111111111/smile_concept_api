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
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PROPOSED => 'Proposed Estimate',
            self::ACCEPTED => 'Accepted / Approved',
            self::COMPLETED => 'Treatment Completed',
            self::REJECTED => 'Rejected',
        };
    }

    /**
     * Statuses this one can transition into.
     *
     * @return TreatmentPlanStatus[]
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT     => [self::PROPOSED, self::REJECTED],
            self::PROPOSED  => [self::ACCEPTED, self::REJECTED, self::DRAFT],
            self::ACCEPTED  => [self::COMPLETED, self::REJECTED],
            self::REJECTED  => [self::DRAFT], // allow reopening
            self::COMPLETED => [],            // terminal
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return empty($this->allowedTransitions());
    }

    public function isEditable(): bool
    {
        // Only draft plans can have their items modified freely
        return $this === self::DRAFT;
    }
}