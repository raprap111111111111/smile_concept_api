<?php

namespace App\Domain\TreatmentPlans\Services;

class TreatmentPlanService
{
    public function validateStepSequence(int $step): void
    {
        if ($step <= 0) {
            throw new \InvalidArgumentException("Treatment plan sequence orders must be positive integers starting from 1.");
        }
    }
}
