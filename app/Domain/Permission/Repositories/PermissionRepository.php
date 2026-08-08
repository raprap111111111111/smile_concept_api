<?php

namespace App\Domain\Permission\Repositories;

use App\Support\Query\BaseRepository;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseRepository
{
    protected string $model = Permission::class;

    // Spatie's `permissions` table has only id/name/guard_name/timestamps —
    // no `description` and no `is_active`. Both were dead config while search
    // and filters no-opped; naming them now would raise "Unknown column".
    protected array $searchable = ['name'];
    protected array $filterable = [];
    protected array $sortable = ['name', 'created_at'];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}