<?php

namespace App\Domain\Services\Actions;

use App\Domain\Services\DTOs\ServiceData;
use App\Domain\Services\Repositories\ServiceRepository;
use App\Domain\Services\Services\ServiceService;
use App\Models\Service;

class UpdateServiceAction
{
    public function __construct(
        private readonly ServiceRepository $repository,
        private readonly ServiceService $service
    ) {}

    public function execute(Service $service, ServiceData $data): Service
    {
        $payload = $data->toArray();

        // Regenerate slug if title changed
        if ($data->title !== $service->title) {
            $payload['slug'] = $this->service->generateSlug($data->title, $service->id);
        }

        // Handle image replacement
        if ($data->image) {
            $this->service->deleteImage($service->image);
            $payload['image'] = $this->service->uploadImage($data->image);
        }

        return $this->repository->update($service, $payload);
    }
}
