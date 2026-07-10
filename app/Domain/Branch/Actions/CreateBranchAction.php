<?php

// app/Domain/Branch/Actions/CreateBranchAction.php

namespace App\Domain\Branch\Actions;

use App\Domain\Branch\DTOs\CreateBranchDTO;
use App\Domain\Branch\Repositories\BranchRepository;
use App\Domain\Branch\Services\BranchService;

class CreateBranchAction
{
    public function __construct(
        private readonly BranchRepository $repository,
        private readonly BranchService $branchService
    ) {}

    public function execute(CreateBranchDTO $dto)
    {
        $code = $this->branchService->generateBranchCode($dto->branchCode, $dto->name);

        return $this->repository->create([
            'name'          => $dto->name,
            'branch_code'   => $code,
            'address'       => $dto->address,
            'city'          => $dto->city,
            'province'      => $dto->province,
            'phone'         => $dto->phone,
            'email'         => $dto->email,
            'is_active'     => $dto->isActive,
            'opening_hours' => $dto->openingHours,
        ]);
    }
}