<?php

namespace App\Domain\Inventories\DTOs;

/**
 * A correction after physically counting a shelf.
 *
 * Carries the counted total, not a delta — that is what the person holding the
 * clipboard actually knows. The action works out the difference.
 */
final readonly class AdjustStockDTO
{
    public function __construct(
        public int $branchId,
        public int $itemId,
        public int $countedQuantity,
        public string $reason,
        public ?string $lotNumber = null,
        public ?string $expiryDate = null,
        public ?string $notes = null,
        public ?int $performedBy = null,
    ) {}
}
