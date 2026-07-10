<?php

namespace App\Domain\Recalls\Services;

use Carbon\Carbon;

class RecallService
{
    public function validateDueDate(string $date): void
    {
        if (Carbon::parse($date)->isPast()) {
            throw new \InvalidArgumentException("Recall due dates must be scheduled in the future.");
        }
    }
}
