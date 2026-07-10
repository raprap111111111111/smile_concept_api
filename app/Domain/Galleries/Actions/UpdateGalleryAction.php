<?php

namespace App\Domain\Galleries\Actions;

use App\Domain\Galleries\DTOs\GalleryData;
use App\Domain\Galleries\Repositories\GalleryRepository;
use App\Domain\Galleries\Services\GalleryService;
use App\Models\Gallery;

class UpdateGalleryAction
{
    public function __construct(
        private readonly GalleryRepository $repository,
        private readonly GalleryService $service
    ) {}

    public function execute(Gallery $gallery, GalleryData $data): Gallery
    {
        $payload = $data->toArray();

        if ($data->image) {
            $this->service->deleteImage($gallery->image);
            $payload['image'] = $this->service->uploadImage($data->image);
        }

        return $this->repository->update($gallery, $payload);
    }
}
