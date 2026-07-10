<?php

namespace App\Domain\Permission\Actions;

use App\Domain\Permission\Repositories\PermissionRepository;
use Spatie\Permission\Models\Permission;

class DeletePermissionAction
{
    public function __construct(
        private readonly PermissionRepository $repository
    ) {}

    public function execute(Permission $permission): bool
    {
        return $this->repository->delete($permission);
    }
}