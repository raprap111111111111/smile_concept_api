<?php

namespace App\Domain\ClinicalNotes\Repositories;

use App\Models\ClinicalNote;
use App\Support\Query\BaseRepository;

class ClinicalNoteRepository extends BaseRepository
{
    protected string $model = ClinicalNote::class;
    protected array $searchable = ['treatment_notes'];
    protected array $filterable = ['appointment_id', 'doctor_id', 'is_locked'];
    protected array $sortable = ['id', 'created_at'];
    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}
