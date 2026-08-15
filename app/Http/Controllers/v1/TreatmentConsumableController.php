<?php

namespace App\Http\Controllers\v1;

use App\Domain\TreatmentConsumables\Actions\SyncTreatmentConsumablesAction;
use App\Domain\TreatmentConsumables\DTOs\TreatmentConsumableDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\Treatment\GetTreatmentConsumablesRequest;
use App\Http\Requests\v1\Treatment\SyncTreatmentConsumablesRequest;
use App\Http\Resources\v1\TreatmentConsumableResource;
use App\Models\Treatment;
use Illuminate\Http\JsonResponse;

/**
 * A treatment's recipe, edited as a whole list rather than row by row — that is
 * how the form panel works, and it makes "remove this line" an ordinary save
 * instead of a separate delete call.
 */
class TreatmentConsumableController extends Controller
{
    public function __construct(
        private readonly SyncTreatmentConsumablesAction $syncAction,
    ) {}

    public function index(
        GetTreatmentConsumablesRequest $request,
        Treatment $treatment,
    ): JsonResponse {
        return $this->successResponse(
            TreatmentConsumableResource::collection(
                $treatment->consumables()->with('item')->get()
            ),
            'Treatment consumables retrieved.',
        );
    }

    public function sync(
        SyncTreatmentConsumablesRequest $request,
        Treatment $treatment,
    ): JsonResponse {
        $lines = array_map(
            fn (array $line): TreatmentConsumableDTO => new TreatmentConsumableDTO(
                itemId: (int) $line['item_id'],
                quantityPerUse: (int) $line['quantity_per_use'],
                isOptional: filter_var($line['is_optional'] ?? false, FILTER_VALIDATE_BOOLEAN),
                notes: $line['notes'] ?? null,
            ),
            $request->validated('consumables'),
        );

        $consumables = $this->syncAction->execute($treatment, $lines);

        return $this->successResponse(
            TreatmentConsumableResource::collection($consumables),
            'Treatment consumables saved.',
        );
    }
}
