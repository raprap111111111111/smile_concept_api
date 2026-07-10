<?php

namespace App\Domain\Services\Repositories;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository
{
    public function all(array $filters = []): Collection
    {
        $query = Service::query()->ordered();

        if (!empty($filters['active_only'])) {
            $query->active();
        }

        if (!empty($filters['featured_only'])) {
            $query->featured();
        }

        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        return $query->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Service::query()->ordered();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Service
    {
        return Service::find($id);
    }

    public function findBySlug(string $slug): ?Service
    {
        return Service::where('slug', $slug)->first();
    }

    public function create(array $data): Service
    {
        return Service::create($data);
    }

    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    public function delete(Service $service): bool
    {
        return (bool) $service->delete();
    }
}
