<?php

namespace App\Domain\RecallTypes\Actions;

use App\Domain\RecallTypes\DTOs\UpdateRecallTypeDTO;
use App\Domain\RecallTypes\Repositories\RecallTypeRepository;
use App\Domain\RecallTypes\Services\RecallTypeService;
use App\Models\RecallType;

class UpdateRecallTypeAction
{
    public function __construct(
        private readonly RecallTypeRepository $repository,
        private readonly RecallTypeService $service
    ) {}

    public function execute(RecallType $type, UpdateRecallTypeDTO $dto)
    {
        if ($dto->frequencyMonths !== null) {
            $this->service->validateRecallFrequency($dto->frequencyMonths);
        }

        $data = array_filter([
            'slug' => $dto->slug,
            'label' => $dto->label,
            'frequency_months' => $dto->frequencyMonths,
            'is_active' => $dto->isActive,
        ], fn($value) => !is_null($value));

        return $this->repository->update($type, $data);
    }
}
