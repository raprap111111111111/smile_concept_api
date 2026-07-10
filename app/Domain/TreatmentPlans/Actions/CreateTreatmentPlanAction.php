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
                $totalEstimate += (float) $treatment->price;

                $itemsData[] = [
                    'treatment_id' => $item->treatmentId,
                    'sequence_order' => $item->sequenceOrder,
                    'estimated_cost' => $treatment->price,
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
