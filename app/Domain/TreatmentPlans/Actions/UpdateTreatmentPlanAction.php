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
                    $totalEstimate += (float) $treatment->price;

                    $plan->items()->create([
                        'treatment_id' => $item->treatmentId,
                        'sequence_order' => $item->sequenceOrder,
                        'estimated_cost' => $treatment->price,
                        'notes' => $item->notes,
                    ]);
                }

                $plan->update(['total_estimated_amount' => $totalEstimate]);
            }

            return $plan->load(['items.treatment', 'patient', 'doctor.user']);
        });
    }
}
