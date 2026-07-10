<?php

namespace App\Domain\LabCases\Actions;

use App\Domain\LabCases\Repositories\LabCaseRepository;
use App\Models\LabCase;

class DeleteLabCaseAction
{
    public function __construct(
        private readonly LabCaseRepository $repository
    ) {}

    public function execute(LabCase $labCase): bool
    {
        return $this->repository->delete($labCase);
    }
}
