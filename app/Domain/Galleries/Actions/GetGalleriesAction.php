<?php

namespace App\Domain\Galleries\Actions;

use App\Domain\Galleries\Repositories\GalleryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GetGalleriesAction
{
    public function __construct(
        private readonly GalleryRepository $repository
    ) {}

    public function execute(array $filters = [], bool $paginate = false, int $perPage = 20): Collection|LengthAwarePaginator
    {
        if ($paginate) {
            return $this->repository->paginate($filters, $perPage);
        }

        return $this->repository->all($filters);
    }
}
