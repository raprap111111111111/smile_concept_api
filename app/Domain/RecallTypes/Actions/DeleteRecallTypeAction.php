<?php

namespace App\Domain\RecallTypes\Actions;

use App\Domain\RecallTypes\Repositories\RecallTypeRepository;
use App\Models\RecallType;

class DeleteRecallTypeAction
{
    public function __construct(
        private readonly RecallTypeRepository $repository
    ) {}

    public function execute(RecallType $type): bool
    {
        return $this->repository->delete($type);
    }
}
