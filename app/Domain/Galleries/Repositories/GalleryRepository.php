<?php

namespace App\Domain\Galleries\Repositories;

use App\Models\Gallery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GalleryRepository
{
    public function all(array $filters = []): Collection
    {
        $query = Gallery::query()->ordered();

        if (!empty($filters['active_only'])) {
            $query->active();
        }

        if (!empty($filters['featured_only'])) {
            $query->featured();
        }

        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get();
    }

    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Gallery::query()->ordered();

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }

        if (!empty($filters['category'])) {
            $query->category($filters['category']);
        }

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Gallery
    {
        return Gallery::find($id);
    }

    public function create(array $data): Gallery
    {
        return Gallery::create($data);
    }

    public function update(Gallery $gallery, array $data): Gallery
    {
        $gallery->update($data);
        return $gallery->fresh();
    }

    public function delete(Gallery $gallery): bool
    {
        return (bool) $gallery->delete();
    }

    public function findManyByIds(array $ids): Collection
    {
        return Gallery::whereIn('id', $ids)->get();
    }
}
