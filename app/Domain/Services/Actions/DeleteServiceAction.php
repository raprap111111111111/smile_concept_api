<?php

namespace App\Domain\Services\Actions;

use App\Domain\Services\Repositories\ServiceRepository;
use App\Domain\Services\Services\ServiceService;
use App\Models\Service;

class DeleteServiceAction
{
    public function __construct(
        private readonly ServiceRepository $repository,
        private readonly ServiceService $service
    ) {}

    public function execute(Service $service): bool
    {
        $this->service->verifyServiceCanBeDeleted($service);
        $this->service->deleteImage($service->image);

        return $this->repository->delete($service);
    }
}
