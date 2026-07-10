<?php

namespace App\Domain\Consents\Repositories;

use App\Models\ConsentTemplate;
use App\Support\Query\BaseRepository;

class ConsentTemplateRepository extends BaseRepository
{
    protected string $model = ConsentTemplate::class;
    protected array $searchable = ['title'];
    protected array $filterable = ['is_active'];
    protected array $sortable = ['id', 'title', 'created_at'];
    protected string $defaultOrderBy = 'title';
    protected string $defaultOrderDirection = 'asc';

    /**
     * Retrieve all consent templates
     */
    public function all()
    {
        return ($this->model)::all();
    }
}
