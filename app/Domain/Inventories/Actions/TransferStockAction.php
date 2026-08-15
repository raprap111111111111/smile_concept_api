<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\DTOs\TransferResult;
use App\Domain\Inventories\DTOs\TransferStockDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Enums\StockMovementType;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

/**
 * Moves stock from one branch to another, lot by lot.
 *
 * Each source batch the move draws from is recreated at the destination with
 * its lot number and expiry intact. Collapsing a transfer into a single
 * undated batch would destroy exactly the information batches exist to carry —
 * the receiving branch would no longer know when the carpules expire.
 */
class TransferStockAction
{
    public function __construct(
        private readonly StockLedger $ledger,
    ) {}

    public function execute(TransferStockDTO $dto): TransferResult
    {
        if ($dto->quantity <= 0) {
            throw new \InvalidArgumentException('Transfer quantity must be greater than zero.');
        }

        if ($dto->fromBranchId === $dto->toBranchId) {
            throw new \InvalidArgumentException('Source and destination branches must differ.');
        }

        return DB::transaction(function () use ($dto): TransferResult {
            $from = Branch::findOrFail($dto->fromBranchId);
            $to   = Branch::findOrFail($dto->toBranchId);

            $outbound = $this->ledger->record(new RecordMovementDTO(
                branchId: $dto->fromBranchId,
                itemId: $dto->itemId,
                type: StockMovementType::TRANSFER_OUT,
                quantityDelta: -$dto->quantity,
                reason: $dto->reason ?? "Transferred to {$to->name}.",
                performedBy: $dto->performedBy,
                notes: $dto->notes,
            ));

            // The one outflow that DOES refuse a shortfall. Consumption records
            // supplies already used, so a gap is history; a transfer is a
            // promise about the future, and you cannot ship what is not there.
            // Throwing here rolls the outflow back with the transaction.
            if ($outbound->hasShortfall()) {
                throw new \RuntimeException(sprintf(
                    '%s does not have %d units to transfer — short by %d.',
                    $from->name,
                    $dto->quantity,
                    $outbound->shortfall,
                ));
            }

            $inbound = [];

            foreach ($outbound->movements as $movement) {
                $sourceBatch = $movement->batch;

                $inbound[] = $this->ledger->record(new RecordMovementDTO(
                    branchId: $dto->toBranchId,
                    itemId: $dto->itemId,
                    type: StockMovementType::TRANSFER_IN,
                    quantityDelta: abs($movement->quantity_delta),
                    lotNumber: $sourceBatch?->lot_number,
                    expiryDate: $sourceBatch?->expiry_date?->toDateString(),
                    receivedAt: now()->toDateString(),
                    reason: $dto->reason ?? "Transferred from {$from->name}.",
                    performedBy: $dto->performedBy,
                    notes: $dto->notes,
                ));
            }

            // Non-empty in practice — a positive quantity with no shortfall
            // always allocates at least one lot — but the fallback keeps this
            // from becoming a fatal if that ever stops holding.
            $lastInbound = $inbound === [] ? null : $inbound[array_key_last($inbound)];

            return new TransferResult(
                outbound: $outbound,
                inbound: $inbound,
                quantity: $dto->quantity,
                sourceBalance: $outbound->balanceAfter,
                destinationBalance: $lastInbound?->balanceAfter ?? 0,
            );
        });
    }
}
