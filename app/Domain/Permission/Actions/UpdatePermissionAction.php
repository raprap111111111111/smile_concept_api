<?php

namespace App\Domain\Permission\Actions;

use App\Domain\Permission\DTOs\UpdatePermissionDTO;
use App\Domain\Permission\Repositories\PermissionRepository;
use Spatie\Permission\Models\Permission;

class UpdatePermissionAction
{
    public function __construct(
        private readonly PermissionRepository $repository
    ) {}

    public function execute(Permission $permission, UpdatePermissionDTO $dto)
    {
        $data = [
            'name'        => $dto->name,
            'description' => $dto->description,
            'is_active'   => $dto->isActive,
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        return $this->repository->update($permission, $data);
    }
}