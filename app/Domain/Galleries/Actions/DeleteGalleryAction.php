<?php

namespace App\Domain\Galleries\Actions;

use App\Domain\Galleries\Repositories\GalleryRepository;
use App\Domain\Galleries\Services\GalleryService;
use App\Models\Gallery;

class DeleteGalleryAction
{
    public function __construct(
        private readonly GalleryRepository $repository,
        private readonly GalleryService $service
    ) {}

    public function execute(Gallery $gallery): bool
    {
        $this->service->deleteGalleryFiles($gallery);
        return $this->repository->delete($gallery);
    }
}
