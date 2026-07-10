<?php

namespace App\Domain\User\Actions;

use App\Domain\User\Repositories\UserRepository;
use App\Models\User;

class DeleteUserAction
{
    public function __construct(
        private readonly UserRepository $repository
    ) {}

    public function execute(User $user): bool
    {
        return $this->repository->delete($user);
    }
}