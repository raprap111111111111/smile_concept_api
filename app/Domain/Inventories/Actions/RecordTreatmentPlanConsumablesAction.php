<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use App\Domain\Inventories\DTOs\ConsumptionResult;
use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Domain\Inventories\Services\StockWatchers;
use App\Enums\StockMovementType;
use App\Enums\TreatmentPlanStatus;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\TreatmentPlan;
use App\Notifications\TreatmentPlanStockShortfallNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Deducts the supplies staff say a completed treatment plan used.
 *
 * The manual sibling of ConsumeAppointmentSuppliesAction: staff review a
 * recipe-prefilled list and submit the actual quantities, so nothing here is
 * gated on the auto-deduct setting — an explicit submission is its own intent.
 *
 * Same two rules as the automatic path:
 *
 *  1. It never blocks on stock. The supplies are already gone; a shortfall is
 *     recorded and flagged rather than refused.
 *  2. It never deducts twice. The ledger is the idempotency record, and the
 *     plan row is locked while checking so two staff submitting at once cannot
 *     both pass the guard.
 */
class RecordTreatmentPlanConsumablesAction
{
    public function __construct(
        private readonly StockLedger $ledger,
        private readonly ActivityLogger $logger,
        private readonly StockWatchers $watchers,
    ) {}

    /**
     * @param array<int, int> $lines item_id => quantity used
     */
    public function execute(TreatmentPlan $plan, int $branchId, array $lines, ?string $notes = null): ConsumptionResult
    {
        if ($plan->status !== TreatmentPlanStatus::COMPLETED) {
            throw new \InvalidArgumentException('Supplies can only be recorded for a completed treatment plan.');
        }

        [$movements, $shortfalls] = DB::transaction(function () use ($plan, $branchId, $lines, $notes): array {
            // Serialize concurrent submissions on the plan row; the guard below
            // is only race-free while this lock is held.
            TreatmentPlan::query()->whereKey($plan->id)->lockForUpdate()->first();

            if ($this->hasAlreadyRecorded($plan)) {
                throw new \RuntimeException(
                    'Supplies were already recorded for this plan. Use a stock adjustment to amend them.'
                );
            }

            $movements = [];
            $shortfalls = [];

            foreach ($lines as $itemId => $quantity) {
                $result = $this->ledger->record(new RecordMovementDTO(
                    branchId: $branchId,
                    itemId: (int) $itemId,
                    type: StockMovementType::CONSUMPTION,
                    quantityDelta: -$quantity,
                    reason: 'Used for treatment plan #' . $plan->id . '.',
                    // Also the idempotency key.
                    referenceType: TreatmentPlan::class,
                    referenceId: (int) $plan->id,
                    performedBy: auth()->id(),
                    notes: $notes,
                ));

                $movements[] = $result;

                if ($result->hasShortfall()) {
                    $shortfalls[(int) $itemId] = $result->shortfall;
                }
            }

            return [$movements, $shortfalls];
        });

        if ($shortfalls !== []) {
            $this->reportShortfall($plan, $branchId, $shortfalls);
        }

        return new ConsumptionResult($movements, $shortfalls, false);
    }

    /**
     * Answered from the ledger, not a flag on the plan — a stamp column would
     * be a second source of truth that can drift from the movements it claims
     * to describe.
     */
    private function hasAlreadyRecorded(TreatmentPlan $plan): bool
    {
        return StockMovement::query()
            ->causedBy(TreatmentPlan::class, (int) $plan->id)
            ->where('type', StockMovementType::CONSUMPTION)
            ->exists();
    }

    /**
     * Shortfall goes out of band — activity log for the audit trail, a
     * notification to branch staff who can restock. The response the client
     * parses stays the same either way.
     *
     * @param array<int, int> $shortfalls
     */
    private function reportShortfall(TreatmentPlan $plan, int $branchId, array $shortfalls): void
    {
        $items = Item::query()
            ->whereIn('id', array_keys($shortfalls))
            ->get()
            ->map(fn (Item $item): array => [
                'item_id'  => $item->id,
                'name'     => $item->name,
                'sku'      => $item->sku,
                'short_by' => $shortfalls[$item->id] ?? 0,
                'unit'     => $item->unit_of_measure,
            ])
            ->values()
            ->all();

        $this->logger->log($plan, 'stock_shortfall', [
            'branch_id' => $branchId,
            'items'     => $items,
        ]);

        $recipients = $this->watchers->at($branchId);

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new TreatmentPlanStockShortfallNotification($plan, $branchId, $items),
            );
        }
    }
}
