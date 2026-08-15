<?php

namespace App\Domain\Inventories\DTOs;

/** Supplies used outside a recorded procedure, logged by staff. */
final readonly class RecordUsageDTO
{
    public function __construct(
        public int $branchId,
        public int $itemId,
        public int $quantity,
        public ?string $reason = null,
        public ?string $notes = null,
        public ?int $performedBy = null,
    ) {}
}
