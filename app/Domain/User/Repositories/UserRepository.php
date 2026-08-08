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
    // `is_active` is not a column on `users` — GetAllUserRequest validates it,
    // but there is nothing to filter against. Listing it here would raise
    // "Unknown column" now that filters are actually applied.
    protected array $filterable = ['branch_id'];
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