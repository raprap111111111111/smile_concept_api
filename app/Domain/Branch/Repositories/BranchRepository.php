<?php

namespace App\Domain\Branch\Repositories;

use App\Domain\Branch\Services\BranchScope;
use App\Models\Branch;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BranchRepository extends BaseRepository
{
    public function __construct(
        private readonly BranchScope $branchScope,
    ) {}

    protected string $model = Branch::class;

    protected array $searchable = [
        'name',
        'branch_code',
        'city',
    ];

    protected array $filterable = [
        'is_active',
        'city',
    ];

    protected array $sortable = [
        'name',
        'branch_code',
        'created_at',
    ];

    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    /**
     * Override base query to always eager-load counts.
     */
    public function query(): Builder
    {
        return parent::query()
            ->withCount(['staff', 'appointments']);
    }

    /**
     * `?mine=1` narrows the list to branches the caller works at.
     *
     * Opt-in rather than always-on: this endpoint feeds every branch picker in
     * the app, and roles without branch_user rows would otherwise be handed an
     * empty dropdown everywhere. The stock screens ask for it because acting on
     * a branch you do not belong to is refused anyway — offering it in a picker
     * only sets up a 403.
     *
     * Scoping by id, not by permission: super-admin comes back unrestricted,
     * and a user attached to nothing correctly gets nothing.
     */
    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        $query = $this->query();

        if (filter_var($params['mine'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            if ($user = Auth::user()) {
                $this->branchScope->apply($query, $user, 'id');
            }
        }

        return $this->paginateQuery($query, $params, $resourceClass);
    }
}