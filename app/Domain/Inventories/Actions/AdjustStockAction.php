<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\Inventories\DTOs\AdjustStockDTO;
use App\Domain\Inventories\DTOs\MovementResult;
use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Enums\StockMovementType;
use App\Models\Inventory;

/**
 * Reconciles the ledger against a physical count.
 *
 * The caller supplies what they counted; the difference is worked out here and
 * written as one signed movement, so the ledger records that a correction
 * happened and why — rather than the old behaviour of overwriting `quantity`
 * with a new number and leaving no trace of the old one.
 */
class AdjustStockAction
{
    public function __construct(
        private readonly StockLedger $ledger,
    ) {}

    public function execute(AdjustStockDTO $dto): MovementResult
    {
        if ($dto->countedQuantity < 0) {
            throw new \InvalidArgumentException('A counted quantity cannot be negative.');
        }

        if (trim($dto->reason) === '') {
            throw new \InvalidArgumentException(
                'A stock adjustment needs a reason — it is the only record of why the count changed.'
            );
        }

        $current = (int) (Inventory::query()
            ->where('branch_id', $dto->branchId)
            ->where('item_id', $dto->itemId)
            ->value('quantity') ?? 0);

        return $this->ledger->record(new RecordMovementDTO(
            branchId: $dto->branchId,
            itemId: $dto->itemId,
            type: StockMovementType::ADJUSTMENT,
            quantityDelta: $dto->countedQuantity - $current,
            // Counting up means units nobody had recorded, so they need a lot to
            // live in. Counting down draws from existing batches and ignores these.
            lotNumber: $dto->lotNumber,
            expiryDate: $dto->expiryDate,
            reason: $dto->reason,
            performedBy: $dto->performedBy,
            notes: $dto->notes,
        ));
    }
}
