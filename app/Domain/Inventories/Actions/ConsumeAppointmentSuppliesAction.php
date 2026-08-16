<?php

namespace App\Domain\Inventories\Actions;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use App\Domain\Inventories\DTOs\ConsumptionResult;
use App\Domain\Inventories\DTOs\InventorySettings;
use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Domain\Inventories\Services\StockWatchers;
use App\Enums\StockMovementType;
use App\Models\Appointment;
use App\Models\Item;
use App\Models\StockMovement;
use App\Notifications\StockShortfallNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Deducts the supplies a completed appointment used.
 *
 * This is what turns the inventory from a list someone maintains by hand into a
 * number that tracks actual patient volume: every procedure on the appointment
 * is looked up in its treatment's recipe, folded into one basket per item, and
 * drawn from the branch's batches earliest-expiry-first.
 *
 * Two rules govern everything here:
 *
 *  1. It never blocks clinical work. Running out of cotton rolls is not a
 *     reason to refuse to check a patient out. A supply that cannot be met is
 *     recorded as a shortfall and flagged; the appointment completes either way.
 *  2. It never deducts twice. The ledger itself is the idempotency record —
 *     see hasAlreadyConsumed().
 */
class ConsumeAppointmentSuppliesAction
{
    public function __construct(
        private readonly StockLedger $ledger,
        private readonly InventorySettings $settings,
        private readonly ActivityLogger $logger,
        private readonly StockWatchers $watchers,
    ) {}

    public function execute(Appointment $appointment): ConsumptionResult
    {
        if (! $this->settings->autoDeductEnabled) {
            return ConsumptionResult::skipped('Automatic deduction is switched off.');
        }

        if ($this->hasAlreadyConsumed($appointment)) {
            return ConsumptionResult::skipped('Supplies were already deducted for this appointment.');
        }

        $basket = $this->basketFor($appointment);

        if ($basket === []) {
            return ConsumptionResult::skipped('No treatment on this appointment has a recipe.');
        }

        $movements = [];
        $shortfalls = [];

        foreach ($basket as $itemId => $quantity) {
            $result = $this->ledger->record(new RecordMovementDTO(
                branchId: (int) $appointment->branch_id,
                itemId: $itemId,
                type: StockMovementType::CONSUMPTION,
                quantityDelta: -$quantity,
                reason: 'Used during appointment #' . $appointment->id . '.',
                // Also the idempotency key.
                referenceType: Appointment::class,
                referenceId: (int) $appointment->id,
                performedBy: auth()->id(),
            ));

            $movements[] = $result;

            if ($result->hasShortfall()) {
                $shortfalls[$itemId] = $result->shortfall;
            }
        }

        $outcome = new ConsumptionResult($movements, $shortfalls, false);

        if ($outcome->hasShortfall()) {
            $this->reportShortfall($appointment, $shortfalls);
        }

        return $outcome;
    }

    /**
     * Has this appointment already been deducted?
     *
     * Answered from the ledger rather than a flag on the appointment. A stamp
     * column would be a second source of truth that can drift from the
     * movements it claims to describe; the ledger cannot disagree with itself.
     * It also survives a partial failure correctly — whatever was written is
     * what is there.
     *
     * This matters because `validateTransition()` treats a same-status change
     * as a no-op and returns rather than throwing, so a repeated
     * `PATCH /appointments/{id}/status` with `completed` re-enters the action.
     */
    private function hasAlreadyConsumed(Appointment $appointment): bool
    {
        return StockMovement::query()
            ->causedBy(Appointment::class, (int) $appointment->id)
            ->where('type', StockMovementType::CONSUMPTION)
            ->exists();
    }

    /**
     * Fold every procedure into one total per item.
     *
     * Two extractions and a filling that both use cotton rolls become a single
     * withdrawal, so the ledger reads as one line per supply rather than one
     * per procedure — and FEFO allocates across the true total instead of
     * splitting batches needlessly.
     *
     * @return array<int, int> item_id => quantity
     */
    private function basketFor(Appointment $appointment): array
    {
        $procedures = $appointment->treatments()
            ->with('treatment.consumables')
            ->get();

        $basket = [];

        foreach ($procedures as $procedure) {
            // How many times this procedure was performed. Defaults to 1 for
            // rows written before the quantity column existed.
            $performed = max(1, (int) ($procedure->quantity ?? 1));

            $consumables = $procedure->treatment?->consumables ?? collect();

            foreach ($consumables as $consumable) {
                // Optional lines are a suggestion for staff, not something to
                // deduct unasked.
                if ($consumable->is_optional) {
                    continue;
                }

                $itemId = (int) $consumable->item_id;
                $units  = (int) $consumable->quantity_per_use * $performed;

                if ($units <= 0) {
                    continue;
                }

                $basket[$itemId] = ($basket[$itemId] ?? 0) + $units;
            }
        }

        return $basket;
    }

    /**
     * Make a shortfall visible without changing the response the client parses.
     *
     * The status-update endpoint returns an AppointmentResource and the Flutter
     * client depends on that shape, so the warning travels out of band: an
     * activity-log entry for the audit trail, and a notification to the people
     * who can actually restock.
     *
     * @param array<int, int> $shortfalls
     */
    private function reportShortfall(Appointment $appointment, array $shortfalls): void
    {
        $items = Item::query()
            ->whereIn('id', array_keys($shortfalls))
            ->get()
            ->map(fn (Item $item): array => [
                'item_id' => $item->id,
                'name'    => $item->name,
                'sku'     => $item->sku,
                'short_by' => $shortfalls[$item->id] ?? 0,
                'unit'    => $item->unit_of_measure,
            ])
            ->values()
            ->all();

        $this->logger->log($appointment, 'stock_shortfall', [
            'branch_id' => $appointment->branch_id,
            'items'     => $items,
        ]);

        $recipients = $this->watchers->at((int) $appointment->branch_id);

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new StockShortfallNotification($appointment, $items),
            );
        }
    }
}
