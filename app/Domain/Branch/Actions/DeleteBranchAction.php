<?php

namespace App\Domain\Branch\Actions;

use App\Domain\Branch\Repositories\BranchRepository;
use App\Models\Branch; // Import the Model

class DeleteBranchAction
{
    public function __construct(
        private readonly BranchRepository $repository
    ) {}

    public function execute(Branch $branch): bool
    {
        return $this->repository->delete($branch);
    }
}