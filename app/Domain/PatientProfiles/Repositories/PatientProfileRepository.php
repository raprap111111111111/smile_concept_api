<?php

namespace App\Domain\PatientProfiles\Repositories;

use App\Models\PatientProfile;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class PatientProfileRepository extends BaseRepository
{
    protected string $model = PatientProfile::class;

    protected array $relations = ['user'];

    // 🔑 EMPTY - so BaseRepository won't auto-apply search
    protected array $searchable = [];

    protected array $filterable = [
        'user_id',
        'blood_type',
    ];

    protected array $sortable = [
        'id',
        'user_id',
        'created_at',
    ];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    /**
     * Override query() to add custom search that includes User fields
     */
    public function query(): Builder
    {
        $query = parent::query();

        $search = request()->input('search');

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Search user table via relationship
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%");
                });

                // Also search patient profile fields
                $q->orWhere('allergies', 'LIKE', "%{$search}%")
                    ->orWhere('medical_history', 'LIKE', "%{$search}%")
                    ->orWhere('emergency_contact_name', 'LIKE', "%{$search}%");
            });
        }

        return $query;
    }

    public function hasExistingProfile(int $userId, ?int $excludeId = null): bool
    {
        $query = ($this->model)::query()->where('user_id', $userId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}