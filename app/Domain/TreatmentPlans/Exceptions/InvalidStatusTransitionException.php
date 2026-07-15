<?php

namespace App\Domain\TreatmentPlans\Exceptions;

use App\Enums\TreatmentPlanStatus;
use DomainException;

class InvalidStatusTransitionException extends DomainException
{
    public static function from(
        TreatmentPlanStatus $current,
        TreatmentPlanStatus $target
    ): self {
        $allowed = array_map(
            fn(TreatmentPlanStatus $s) => $s->value,
            $current->allowedTransitions()
        );

        $msg = empty($allowed)
            ? "Cannot change status: '{$current->value}' is a terminal state."
            : "Cannot change status from '{$current->value}' to '{$target->value}'. "
              . 'Allowed: ' . implode(', ', $allowed);

        return new self($msg);
    }
}