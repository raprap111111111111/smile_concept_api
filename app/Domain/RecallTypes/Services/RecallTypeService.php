<?php

namespace App\Domain\RecallTypes\Services;

class RecallTypeService
{
    public function validateRecallFrequency(int $months): void
    {
        if ($months <= 0) {
            throw new \InvalidArgumentException("Recall frequency interval months must be at least 1 month.");
        }
    }
}
