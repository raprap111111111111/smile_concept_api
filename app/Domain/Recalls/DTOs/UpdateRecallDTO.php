<?php

namespace App\Domain\Recalls\DTOs;

final readonly class UpdateRecallDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?int $recallTypeId = null,
        public ?string $dueDate = null,
        public ?string $status = null,
        public ?string $lastNotifiedAt = null
    ) {}
}
