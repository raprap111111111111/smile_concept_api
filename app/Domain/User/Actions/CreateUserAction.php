<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\Services\UserService;

class CreateUserAction
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserService $userService
    ) {}

    public function execute(CreateUserDTO $dto)
    {
        return $this->repository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'branch_id' => $dto->branchId,
            'password' => $this->userService->hashPassword($dto->password),
            'is_active' => $dto->isActive,
        ]);
    }
}