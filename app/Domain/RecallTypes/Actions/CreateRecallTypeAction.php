<?php

namespace App\Domain\RecallTypes\Actions;

use App\Domain\RecallTypes\DTOs\CreateRecallTypeDTO;
use App\Domain\RecallTypes\Repositories\RecallTypeRepository;
use App\Domain\RecallTypes\Services\RecallTypeService;

class CreateRecallTypeAction
{
    public function __construct(
        private readonly RecallTypeRepository $repository,
        private readonly RecallTypeService $service
    ) {}

    public function execute(CreateRecallTypeDTO $dto)
    {
        $this->service->validateRecallFrequency($dto->frequencyMonths);

        return $this->repository->create([
            'slug' => $dto->slug,
            'label' => $dto->label,
            'frequency_months' => $dto->frequencyMonths,
            'is_active' => $dto->isActive,
        ]);
    }
}
