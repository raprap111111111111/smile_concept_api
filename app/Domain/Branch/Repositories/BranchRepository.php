<?php

namespace App\Domain\Branch\Repositories;

use App\Models\Branch;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class BranchRepository extends BaseRepository
{
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
}