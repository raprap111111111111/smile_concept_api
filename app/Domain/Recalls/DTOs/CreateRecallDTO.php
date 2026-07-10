<?php

namespace App\Domain\Recalls\DTOs;

final readonly class CreateRecallDTO
{
    public function __construct(
        public int $userId,
        public int $recallTypeId,
        public string $dueDate,
        public string $status
    ) {}
}
