<?php

namespace App\Domain\Inventories\DTOs;

use App\Models\StockMovement;

/**
 * What a ledger write actually did.
 *
 * One request can produce several movements: drawing 6 carpules across two
 * batches writes two rows, and a third if stock ran out partway.
 */
final readonly class MovementResult
{
    /**
     * @param list<StockMovement> $movements Ledger rows written, in the order applied.
     * @param int $shortfall Units that no batch could satisfy. Always 0 for inflows.
     * @param int $balanceAfter Branch+item quantity once this write settled.
     */
    public function __construct(
        public array $movements,
        public int $shortfall,
        public int $balanceAfter,
    ) {}

    public static function empty(int $balanceAfter): self
    {
        return new self([], 0, $balanceAfter);
    }

    /** True when stock ran out mid-write. Never a failure — only a warning. */
    public function hasShortfall(): bool
    {
        return $this->shortfall > 0;
    }
}
