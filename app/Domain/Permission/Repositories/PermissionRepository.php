<?php

namespace App\Domain\Permission\Repositories;

use App\Support\Query\BaseRepository;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseRepository
{
    protected string $model = Permission::class;

    protected array $searchable = ['name', 'description'];
    protected array $filterable = ['is_active'];
    protected array $sortable = ['name', 'created_at'];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}