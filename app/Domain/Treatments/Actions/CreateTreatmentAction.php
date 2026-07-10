<?php

namespace App\Domain\Treatments\Actions;

use App\Domain\Treatments\DTOs\CreateTreatmentDTO;
use App\Domain\Treatments\Repositories\TreatmentRepository;
use App\Domain\Treatments\Services\TreatmentService;

class CreateTreatmentAction
{
    public function __construct(
        private readonly TreatmentRepository $repository,
        private readonly TreatmentService $service
    ) {}

    public function execute(CreateTreatmentDTO $dto)
    {
        $this->service->validatePricingAndDuration($dto->price, $dto->estimatedDurationMinutes);

        return $this->repository->create([
            'name' => $dto->name,
            'description' => $dto->description,
            'price' => $dto->price,
            'estimated_duration_minutes' => $dto->estimatedDurationMinutes,
            'is_active' => $dto->isActive,
        ]);
    }
}
