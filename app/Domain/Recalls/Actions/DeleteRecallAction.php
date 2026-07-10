<?php

namespace App\Domain\Recalls\Actions;

use App\Domain\Recalls\Repositories\RecallRepository;
use App\Models\Recall;

class DeleteRecallAction
{
    public function __construct(
        private readonly RecallRepository $repository
    ) {}

    public function execute(Recall $recall): bool
    {
        return $this->repository->delete($recall);
    }
}
