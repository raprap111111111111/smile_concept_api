<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\Services\UserService;
use Illuminate\Support\Facades\DB;

class CreateUserAction
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserService $userService
    ) {}

    public function execute(CreateUserDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->repository->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'branch_id' => $dto->branchId,
                'password' => $this->userService->hashPassword($dto->password),
                'is_active' => $dto->isActive,
            ]);

            // Without this the account has no role and therefore no
            // permissions — it can log in but do nothing.
            $user->assignRole($dto->role);

            return $user;
        });
    }
}