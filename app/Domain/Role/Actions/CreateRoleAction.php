<?php

namespace App\Domain\Role\Actions;

use App\Domain\Role\DTOs\CreateRoleDTO;
use App\Domain\Role\Repositories\RoleRepository;

class CreateRoleAction
{
    public function __construct(
        private readonly RoleRepository $repository
    ) {}

    public function execute(CreateRoleDTO $dto)
    {
        return $this->repository->create([
            'name'        => $dto->name,
            'description' => $dto->description,
            'is_active'   => $dto->isActive,
            'guard_name'  => 'api',
        ]);
    }
}