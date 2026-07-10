<?php

namespace App\Domain\Branch\Actions;

use App\Domain\Branch\DTOs\UpdateBranchDTO;
use App\Domain\Branch\Repositories\BranchRepository;
use App\Models\Branch;

class UpdateBranchAction
{
    public function __construct(
        private readonly BranchRepository $repository
    ) {}


    public function execute(Branch $branch, UpdateBranchDTO $dto)
    {
        // Define all data from the DTO
        $data = [
            'name'          => $dto->name,
            'branch_code'   => $dto->branchCode,
            'address'       => $dto->address,
            'city'          => $dto->city,
            'province'      => $dto->province,
            'phone'         => $dto->phone,
            'email'         => $dto->email,
            'is_active'     => $dto->isActive,
            'opening_hours' => $dto->openingHours,
        ];

        // Filter ONLY the fields that are null (to keep existing database values)
        $data = array_filter($data, fn($value) => !is_null($value));

        return $this->repository->update($branch, $data);
    }
}
