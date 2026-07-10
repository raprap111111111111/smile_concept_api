<?php

namespace App\Domain\Services\Actions;

use App\Domain\Services\DTOs\ServiceData;
use App\Domain\Services\Repositories\ServiceRepository;
use App\Domain\Services\Services\ServiceService;
use App\Models\Service;

class CreateServiceAction
{
    public function __construct(
        private readonly ServiceRepository $repository,
        private readonly ServiceService $service
    ) {}

    public function execute(ServiceData $data): Service
    {
        $payload = $data->toArray();

        // Generate unique slug
        $payload['slug'] = $this->service->generateSlug($data->title);

        // Handle image upload
        if ($data->image) {
            $payload['image'] = $this->service->uploadImage($data->image);
        }

        return $this->repository->create($payload);
    }
}
