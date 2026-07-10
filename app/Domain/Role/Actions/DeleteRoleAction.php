<?php

namespace App\Domain\Role\Actions;

use App\Domain\Role\Repositories\RoleRepository;
use Spatie\Permission\Models\Role;

class DeleteRoleAction
{
    public function __construct(
        private readonly RoleRepository $repository
    ) {}

    public function execute(Role $role): bool
    {
        return $this->repository->delete($role);
    }
}