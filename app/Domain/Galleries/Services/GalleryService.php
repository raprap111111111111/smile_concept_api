<?php

namespace App\Domain\Galleries\Services;

use App\Models\Gallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GalleryService
{
    private const STORAGE_PATH = 'gallery';
    private const DISK = 'public';

    public function uploadImage(UploadedFile $image): string
    {
        return $image->store(self::STORAGE_PATH, self::DISK);
    }

    public function deleteImage(?string $imagePath): void
    {
        if ($imagePath && Storage::disk(self::DISK)->exists($imagePath)) {
            Storage::disk(self::DISK)->delete($imagePath);
        }
    }

    public function deleteGalleryFiles(Gallery $gallery): void
    {
        $this->deleteImage($gallery->image);
        $this->deleteImage($gallery->thumbnail);
    }
}
