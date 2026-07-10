<?php

namespace App\Domain\LabCases\Repositories;

use App\Models\LabCase;
use App\Support\Query\BaseRepository;

class LabCaseRepository extends BaseRepository
{
    protected string $model = LabCase::class;
    protected array $searchable = ['lab_name', 'work_type'];
    protected array $filterable = ['appointment_id', 'status'];
    protected array $sortable = ['id', 'due_date', 'sent_date'];
    protected string $defaultOrderBy = 'due_date';
    protected string $defaultOrderDirection = 'asc';
}
