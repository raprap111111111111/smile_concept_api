<?php

namespace App\Domain\Consents\Repositories;

use App\Models\ConsentTemplate;
use App\Support\Query\BaseRepository;
use Illuminate\Support\Facades\Auth;

class ConsentTemplateRepository extends BaseRepository
{
    protected string $model = ConsentTemplate::class;
    protected array $searchable = ['title'];
    protected array $filterable = ['is_active'];
    protected array $sortable = ['id', 'title', 'created_at'];
    protected string $defaultOrderBy = 'title';
    protected string $defaultOrderDirection = 'asc';

    public function paginate(array $params = [], ?string $resourceClass = null): array
    {
        $user = Auth::user();

        // Non-admin users only see active templates
        if ($user && ! $user->can('consent-form.viewAny')) {
            $params['is_active'] = true;
        }

        return parent::paginate($params, $resourceClass);
    }

    public function activeOnly()
    {
        return ($this->model)::where('is_active', true)
            ->orderBy('title')
            ->get();
    }
}