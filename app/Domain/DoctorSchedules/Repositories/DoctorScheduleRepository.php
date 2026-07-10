<?php

namespace App\Domain\DoctorSchedules\Repositories;

use App\Enums\DayOfWeek;
use App\Models\DoctorSchedule;
use App\Support\Query\BaseRepository;

class DoctorScheduleRepository extends BaseRepository
{
    protected string $model = DoctorSchedule::class;

    protected array $searchable = [];

    protected array $filterable = [
        'doctor_id',
        'branch_id',
        'day_of_week',
    ];

    protected array $sortable = [
        'id',
        'day_of_week',
        'start_time',
        'created_at',
    ];

    protected string $defaultOrderBy = 'day_of_week';
    protected string $defaultOrderDirection = 'asc';

    /**
     * Check if a doctor has overlapping schedule intervals on the same day
     */
    public function hasOverlappingSchedule(
        int $doctorId, 
        DayOfWeek $dayOfWeek, 
        string $startTime, 
        string $endTime, 
        ?int $excludeId = null
    ): bool {
        // Resolve Intelephense issue using static class instantiation syntax
        $query = ($this->model)::query()
            ->where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek->value) 
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($sub) use ($startTime, $endTime) {
                    $sub->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
