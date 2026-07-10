<?php

namespace App\Domain\Permission\Actions;

use App\Domain\Permission\DTOs\CreatePermissionDTO;
use App\Domain\Permission\Repositories\PermissionRepository;

class CreatePermissionAction
{
    public function __construct(
        private readonly PermissionRepository $repository
    ) {}

    public function execute(CreatePermissionDTO $dto)
    {
        return $this->repository->create([
            'name'        => $dto->name,
            'description' => $dto->description,
            'is_active'   => $dto->isActive,
            'guard_name'  => 'web',
        ]);
    }
}