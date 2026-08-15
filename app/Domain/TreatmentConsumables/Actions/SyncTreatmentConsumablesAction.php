<?php

namespace App\Domain\TreatmentConsumables\Actions;

use App\Domain\TreatmentConsumables\DTOs\TreatmentConsumableDTO;
use App\Models\Treatment;
use App\Models\TreatmentConsumable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Replaces a treatment's recipe with the list the form submitted.
 *
 * A true sync rather than delete-and-recreate: lines that stayed are updated in
 * place, so their ids and audit history survive an unrelated edit. Recreating
 * everything on each save — as UpdateTreatmentPlanAction does for plan items —
 * would fill the activity log with churn every time someone changed one number,
 * and this model is audited precisely because changing a recipe silently
 * changes what every future appointment deducts.
 */
class SyncTreatmentConsumablesAction
{
    /**
     * @param list<TreatmentConsumableDTO> $lines
     * @return Collection<int, TreatmentConsumable>
     */
    public function execute(Treatment $treatment, array $lines): Collection
    {
        return DB::transaction(function () use ($treatment, $lines): Collection {
            $keep = [];

            foreach ($lines as $line) {
                if ($line->quantityPerUse <= 0) {
                    // A zero-quantity line is a removal expressed clumsily, not
                    // an instruction to deduct nothing forever.
                    continue;
                }

                $consumable = TreatmentConsumable::updateOrCreate(
                    [
                        'treatment_id' => $treatment->id,
                        'item_id'      => $line->itemId,
                    ],
                    [
                        'quantity_per_use' => $line->quantityPerUse,
                        'is_optional'      => $line->isOptional,
                        'notes'            => $line->notes,
                    ],
                );

                $keep[] = $consumable->id;
            }

            $treatment->consumables()
                ->whereNotIn('id', $keep === [] ? [0] : $keep)
                ->delete();

            return $treatment->consumables()->with('item')->get();
        });
    }
}
