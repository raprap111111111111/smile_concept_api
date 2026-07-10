<?php

namespace App\Domain\ActivityLogs\Repositories;

use App\Models\ActivityLog;
use App\Support\Query\BaseRepository;

class ActivityLogRepository extends BaseRepository
{
    protected string $model = ActivityLog::class;

    protected array $with = ['user'];

    protected array $searchable = ['action', 'subject_type', 'ip_address'];

    protected array $filterable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
    ];

    protected array $sortable = ['id', 'action', 'created_at'];

    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}