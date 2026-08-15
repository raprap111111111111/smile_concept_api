<?php

namespace App\Domain\TreatmentPlans\Actions;

use App\Domain\TreatmentPlans\DTOs\UpdateTreatmentPlanDTO;
use App\Domain\TreatmentPlans\Repositories\TreatmentPlanRepository;
use App\Domain\TreatmentPlans\Services\TreatmentPlanService;
use App\Models\TreatmentPlan;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class UpdateTreatmentPlanAction
{
    public function __construct(
        private readonly TreatmentPlanRepository $repository,
        private readonly TreatmentPlanService $service
    ) {}

    public function execute(TreatmentPlan $plan, UpdateTreatmentPlanDTO $dto)
    {
        if ($dto->items !== null) {
            foreach ($dto->items as $item) {
                $this->service->validateStepSequence($item->sequenceOrder);
            }
        }

        return DB::transaction(function () use ($plan, $dto) {
            $parentData = array_filter([
                'user_id' => $dto->userId,
                'doctor_id' => $dto->doctorId,
                'name' => $dto->name,
                'status' => $dto->status,
                'notes' => $dto->notes,
            ], fn($value) => !is_null($value));

            $this->repository->update($plan, $parentData);

            if ($dto->items !== null) {
                $plan->items()->delete();
                $totalEstimate = 0.00;

                foreach ($dto->items as $item) {
                    $treatment = Treatment::findOrFail($item->treatmentId);

                    // Re-quoted against today's catalog price, since replacing
                    // the items is an explicit re-write of the plan.
                    $unitPrice = (float) $treatment->price;
                    $lineTotal = $unitPrice * $item->quantity;
                    $totalEstimate += $lineTotal;

                    $plan->items()->create([
                        'treatment_id' => $item->treatmentId,
                        'sequence_order' => $item->sequenceOrder,
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'estimated_cost' => $lineTotal,
                        'notes' => $item->notes,
                    ]);
                }

                $plan->update(['total_estimated_amount' => $totalEstimate]);
            }

            return $plan->load(['items.treatment', 'patient', 'doctor.user']);
        });
    }
}
