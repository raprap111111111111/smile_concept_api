<?php

namespace App\Domain\Galleries\Actions;

use App\Domain\Galleries\Repositories\GalleryRepository;
use App\Domain\Galleries\Services\GalleryService;

class BulkDeleteGalleryAction
{
    public function __construct(
        private readonly GalleryRepository $repository,
        private readonly GalleryService $service
    ) {}

    public function execute(array $ids): int
    {
        $galleries = $this->repository->findManyByIds($ids);
        $deletedCount = 0;

        foreach ($galleries as $gallery) {
            $this->service->deleteGalleryFiles($gallery);
            $this->repository->delete($gallery);
            $deletedCount++;
        }

        return $deletedCount;
    }
}
