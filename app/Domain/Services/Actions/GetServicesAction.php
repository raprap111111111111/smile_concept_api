<?php

namespace App\Domain\Services\Actions;

use App\Domain\Services\Repositories\ServiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GetServicesAction
{
    public function __construct(
        private readonly ServiceRepository $repository
    ) {}

    public function execute(array $filters = [], bool $paginate = false, int $perPage = 15): Collection|LengthAwarePaginator
    {
        if ($paginate) {
            return $this->repository->paginate($filters, $perPage);
        }

        return $this->repository->all($filters);
    }
}
