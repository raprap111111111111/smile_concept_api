<?php

namespace App\Domain\Recalls\Repositories;

use App\Models\Recall;
use App\Support\Query\BaseRepository;

class RecallRepository extends BaseRepository
{
    protected string $model = Recall::class;
    protected array $searchable = ['recall_type'];
    protected array $filterable = ['user_id', 'status'];
    protected array $sortable = ['id', 'due_date', 'last_notified_at'];
    protected string $defaultOrderBy = 'due_date';
    protected string $defaultOrderDirection = 'asc';
}
