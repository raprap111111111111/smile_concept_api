<?php

namespace App\Domain\TreatmentPlans\Actions;

use App\Domain\TreatmentPlans\DTOs\CreateTreatmentPlanDTO;
use App\Domain\TreatmentPlans\Repositories\TreatmentPlanRepository;
use App\Domain\TreatmentPlans\Services\TreatmentPlanService;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;

class CreateTreatmentPlanAction
{
    public function __construct(
        private readonly TreatmentPlanRepository $repository,
        private readonly TreatmentPlanService $service
    ) {}

    public function execute(CreateTreatmentPlanDTO $dto)
    {
        foreach ($dto->items as $item) {
            $this->service->validateStepSequence($item->sequenceOrder);
        }

        return DB::transaction(function () use ($dto) {
            $totalEstimate = 0.00;
            $itemsData = [];

            foreach ($dto->items as $item) {
                $treatment = Treatment::findOrFail($item->treatmentId);

                // unit_price freezes the catalog price at quote time; a later
                // catalog change must not move an already-quoted plan.
                $unitPrice = (float) $treatment->price;
                $lineTotal = $unitPrice * $item->quantity;
                $totalEstimate += $lineTotal;

                $itemsData[] = [
                    'treatment_id' => $item->treatmentId,
                    'sequence_order' => $item->sequenceOrder,
                    'quantity' => $item->quantity,
                    'unit_price' => $unitPrice,
                    'estimated_cost' => $lineTotal,
                    'notes' => $item->notes,
                ];
            }

            $plan = $this->repository->create([
                'user_id' => $dto->userId,
                'doctor_id' => $dto->doctorId,
                'name' => $dto->name,
                'status' => 'proposed',
                'total_estimated_amount' => $totalEstimate,
                'notes' => $dto->notes,
            ]);

            foreach ($itemsData as $item) {
                $plan->items()->create($item);
            }

            return $plan->load(['items.treatment', 'patient', 'doctor.user']);
        });
    }
}
