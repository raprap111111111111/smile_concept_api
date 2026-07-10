<?php

namespace App\Domain\Settings\Repositories;

use App\Models\Setting;
use App\Support\Query\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class SettingRepository extends BaseRepository
{
    protected string $model = Setting::class;

    protected array $searchable = ['key', 'label', 'description'];

    protected array $filterable = [
        'group',
        'type',
        'is_public',
        'is_editable',
    ];

    protected array $sortable = [
        'id',
        'key',
        'group',
        'created_at',
    ];

    protected string $defaultOrderBy        = 'group';
    protected string $defaultOrderDirection = 'asc';

    public function findByKey(string $key): ?Setting
    {
        return Setting::where('key', $key)->first();
    }

    public function getPublicSettings(): Collection
    {
        return Setting::where('is_public', true)->get();
    }
}