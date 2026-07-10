<?php

namespace App\Domain\User\Repositories;

use App\Models\User;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends BaseRepository
{
    protected string $model = User::class;

    protected array $relations  = ['roles', 'branches', 'patientProfile'];
    protected array $searchable = ['name', 'email', 'phone'];
    protected array $filterable = ['branch_id', 'is_active'];
    protected array $sortable   = ['name', 'email', 'created_at'];

    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    public function query(): Builder
    {
        $query = parent::query();

        $request = request();

        // Filter by role
        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        // Exclude by role (must ALSO have at least one role)
        if ($excludeRole = $request->input('exclude_role')) {
            $query
                ->whereHas('roles') // 🔑 must have at least one role
                ->whereDoesntHave(
                    'roles',
                    fn ($q) => $q->where('name', $excludeRole)
                );
        }

        return $query;
    }
}