<?php

namespace App\Http\Controllers\v1;

use App\Domain\Inventories\Actions\RecordTreatmentPlanConsumablesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\TreatmentPlan\GetTreatmentPlanConsumablesRequest;
use App\Http\Requests\v1\TreatmentPlan\RecordTreatmentPlanConsumablesRequest;
use App\Http\Resources\v1\StockMovementResource;
use App\Enums\StockMovementType;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\TreatmentPlan;
use Illuminate\Http\JsonResponse;

/**
 * Supplies used for a completed treatment plan.
 *
 * GET answers the recording dialog: what the recipes suggest, and what was
 * already recorded. POST writes the ledger once; amendments go through the
 * stock adjustment flow, so there is deliberately no update or destroy.
 */
class TreatmentPlanConsumableController extends Controller
{
    public function __construct(
        private readonly RecordTreatmentPlanConsumablesAction $recordAction,
    ) {}

    public function index(GetTreatmentPlanConsumablesRequest $request, TreatmentPlan $treatmentPlan): JsonResponse
    {
        $movements = $this->recordedMovements($treatmentPlan);

        return $this->successResponse([
            'recorded'        => $movements->isNotEmpty(),
            'movements'       => StockMovementResource::collection($movements),
            'suggested_lines' => $this->suggestedLines($treatmentPlan),
        ], 'Treatment plan consumables retrieved.');
    }

    public function store(RecordTreatmentPlanConsumablesRequest $request, TreatmentPlan $treatmentPlan): JsonResponse
    {
        $lines = collect($request->validated('lines'))
            ->mapWithKeys(fn (array $line): array => [(int) $line['item_id'] => (int) $line['quantity']])
            ->all();

        try {
            $result = $this->recordAction->execute(
                $treatmentPlan,
                (int) $request->validated('branch_id'),
                $lines,
                $request->validated('notes'),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 409);
        }

        $shortfalls = $this->shortfallItems($result->shortfalls);

        $message = $result->hasShortfall()
            ? sprintf(
                'Supplies recorded, but %d item(s) ran short. Their balances are now negative.',
                count($shortfalls),
            )
            : 'Supplies recorded.';

        return $this->successResponse([
            'recorded'   => true,
            'movements'  => StockMovementResource::collection($this->recordedMovements($treatmentPlan)),
            'shortfalls' => $shortfalls,
        ], $message, JsonResponse::HTTP_CREATED);
    }

    /** Everything the ledger holds against this plan, ready for the resource. */
    private function recordedMovements(TreatmentPlan $treatmentPlan)
    {
        return StockMovement::query()
            ->with(['item', 'branch', 'batch', 'performer'])
            ->causedBy(TreatmentPlan::class, (int) $treatmentPlan->id)
            ->where('type', StockMovementType::CONSUMPTION)
            ->orderBy('id')
            ->get();
    }

    /**
     * What the recipes say this plan should have used: quantity_per_use times
     * how often each procedure was planned, folded into one line per item.
     * Optional recipe lines are included as suggestions, flagged; when an item
     * is optional in one recipe and required in another, required wins.
     *
     * @return list<array<string, mixed>>
     */
    private function suggestedLines(TreatmentPlan $treatmentPlan): array
    {
        $treatmentPlan->load(['items.treatment.consumables.item']);

        $lines = [];

        foreach ($treatmentPlan->items as $step) {
            // Rows written before the quantity column existed default to 1.
            $performed = max(1, (int) ($step->quantity ?? 1));

            $consumables = $step->treatment?->consumables ?? collect();

            foreach ($consumables as $consumable) {
                $units = (int) $consumable->quantity_per_use * $performed;

                if ($units <= 0 || $consumable->item === null) {
                    continue;
                }

                $itemId = (int) $consumable->item_id;

                if (! isset($lines[$itemId])) {
                    $lines[$itemId] = [
                        'item_id'            => $itemId,
                        'name'               => $consumable->item->name,
                        'sku'                => $consumable->item->sku,
                        'unit_of_measure'    => $consumable->item->unit_of_measure,
                        'is_optional'        => (bool) $consumable->is_optional,
                        'suggested_quantity' => 0,
                    ];
                }

                $lines[$itemId]['suggested_quantity'] += $units;
                $lines[$itemId]['is_optional'] = $lines[$itemId]['is_optional'] && $consumable->is_optional;
            }
        }

        return array_values($lines);
    }

    /**
     * @param array<int, int> $shortfalls item_id => unmet units
     * @return list<array<string, mixed>>
     */
    private function shortfallItems(array $shortfalls): array
    {
        if ($shortfalls === []) {
            return [];
        }

        return Item::query()
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
    }
}
