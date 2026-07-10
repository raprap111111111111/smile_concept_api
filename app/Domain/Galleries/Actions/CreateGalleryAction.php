<?php

namespace App\Domain\Galleries\Actions;

use App\Domain\Galleries\DTOs\GalleryData;
use App\Domain\Galleries\Repositories\GalleryRepository;
use App\Domain\Galleries\Services\GalleryService;
use App\Models\Gallery;

class CreateGalleryAction
{
    public function __construct(
        private readonly GalleryRepository $repository,
        private readonly GalleryService $service
    ) {}

    public function execute(GalleryData $data): Gallery
    {
        $payload = $data->toArray();
        $payload['image'] = $this->service->uploadImage($data->image);

        return $this->repository->create($payload);
    }
}
