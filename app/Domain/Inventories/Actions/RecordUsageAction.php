<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\Inventories\DTOs\MovementResult;
use App\Domain\Inventories\DTOs\RecordUsageDTO;
use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Enums\StockMovementType;

/**
 * Logs supplies used outside a recorded procedure — a spill, a demo, anything
 * the treatment recipes will not account for.
 *
 * Like automatic consumption, this does not refuse when stock runs short: the
 * supplies are already gone, and rejecting the entry would only mean the ledger
 * disagrees with the cupboard. The shortfall is recorded instead.
 */
class RecordUsageAction
{
    public function __construct(
        private readonly StockLedger $ledger,
    ) {}

    public function execute(RecordUsageDTO $dto): MovementResult
    {
        if ($dto->quantity <= 0) {
            throw new \InvalidArgumentException('Usage quantity must be greater than zero.');
        }

        return $this->ledger->record(new RecordMovementDTO(
            branchId: $dto->branchId,
            itemId: $dto->itemId,
            type: StockMovementType::MANUAL_USAGE,
            quantityDelta: -$dto->quantity,
            reason: $dto->reason,
            performedBy: $dto->performedBy,
            notes: $dto->notes,
        ));
    }
}
