<?php

namespace App\Domain\ToothConditions\Actions;

use App\Domain\ToothConditions\Repositories\ToothConditionRepository;
use App\Models\ToothCondition;

class DeleteToothConditionAction
{
    public function __construct(
        private readonly ToothConditionRepository $repository
    ) {}

    public function execute(ToothCondition $condition): bool
    {
        return $this->repository->delete($condition);
    }
}
