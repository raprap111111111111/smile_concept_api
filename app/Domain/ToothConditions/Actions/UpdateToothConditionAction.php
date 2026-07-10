<?php

namespace App\Domain\ToothConditions\Actions;

use App\Domain\ToothConditions\DTOs\UpdateToothConditionDTO;
use App\Domain\ToothConditions\Repositories\ToothConditionRepository;
use App\Models\ToothCondition;

class UpdateToothConditionAction
{
    public function __construct(
        private readonly ToothConditionRepository $repository
    ) {}

    public function execute(ToothCondition $condition, UpdateToothConditionDTO $dto)
    {
        $data = [
            'slug' => $dto->slug,
            'label' => $dto->label,
            'color_code' => $dto->colorCode,
            'is_active' => $dto->isActive,
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        return $this->repository->update($condition, $data);
    }
}
