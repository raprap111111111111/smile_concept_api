<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DTOs\UpdateUserDTO;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\Services\UserService;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    public function __construct(
        private readonly UserRepository $repository,
        private readonly UserService $userService
    ) {}

    public function execute(User $user, UpdateUserDTO $dto)
    {
        // 1. Prepare basic user data
        $data = [
            'name'      => $dto->name,
            'email'     => $dto->email,
            'phone'     => $dto->phone,
            'is_active' => $dto->isActive,
        ];

        // 2. Hash password if provided
        if ($dto->password) {
            $data['password'] = $this->userService->hashPassword($dto->password);
        }

        // 3. Remove null values from data array
        $data = array_filter($data, fn($value) => !is_null($value));

        // 4. Use a transaction to ensure both user update and branch sync succeed
        return DB::transaction(function () use ($user, $data, $dto) {
            // Update the user basic info
            $updatedUser = $this->repository->update($user, $data);

            // Sync the many-to-many relationship if branchIds were provided
            if (isset($dto->branchIds)) {
                $updatedUser->branches()->sync($dto->branchIds);
            }

            // Return the user with the branches relationship loaded
            return $updatedUser->load('branches');
        });
    }
}