<?php

namespace App\Domain\Inventories\DTOs;

/** Moving stock between branches. */
final readonly class TransferStockDTO
{
    public function __construct(
        public int $fromBranchId,
        public int $toBranchId,
        public int $itemId,
        public int $quantity,
        public ?string $reason = null,
        public ?string $notes = null,
        public ?int $performedBy = null,
    ) {}
}
