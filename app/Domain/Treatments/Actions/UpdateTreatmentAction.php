<?php

namespace App\Domain\Treatments\Actions;

use App\Domain\Treatments\DTOs\UpdateTreatmentDTO;
use App\Domain\Treatments\Repositories\TreatmentRepository;
use App\Domain\Treatments\Services\TreatmentService;
use App\Models\Treatment;

class UpdateTreatmentAction
{
    public function __construct(
        private readonly TreatmentRepository $repository,
        private readonly TreatmentService $service
    ) {}

    public function execute(Treatment $treatment, UpdateTreatmentDTO $dto)
    {
        $price = $dto->price ?? (float) $treatment->price;
        $duration = $dto->estimatedDurationMinutes ?? $treatment->estimated_duration_minutes;

        $this->service->validatePricingAndDuration($price, $duration);

        $data = array_filter([
            'name' => $dto->name,
            'description' => $dto->description,
            'price' => $dto->price,
            'estimated_duration_minutes' => $dto->estimatedDurationMinutes,
            'is_active' => $dto->isActive,
        ], fn($value) => !is_null($value));

        return $this->repository->update($treatment, $data);
    }
}
