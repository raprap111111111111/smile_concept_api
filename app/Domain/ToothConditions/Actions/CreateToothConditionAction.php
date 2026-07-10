<?php

namespace App\Domain\ToothConditions\Actions;

use App\Domain\ToothConditions\DTOs\CreateToothConditionDTO;
use App\Domain\ToothConditions\Repositories\ToothConditionRepository;

class CreateToothConditionAction
{
    public function __construct(
        private readonly ToothConditionRepository $repository
    ) {}

    public function execute(CreateToothConditionDTO $dto)
    {
        return $this->repository->create([
            'slug' => $dto->slug,
            'label' => $dto->label,
            'color_code' => $dto->colorCode,
            'is_active' => $dto->isActive,
        ]);
    }
}
