<?php

namespace App\Domain\Services\Services;

use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceService
{
    private const STORAGE_PATH = 'services';
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

    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while ($this->slugExists($slug, $excludeId)) {
            $slug = "{$original}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $query = Service::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function verifyServiceCanBeDeleted(Service $service): void
    {
        // Add business rules here, e.g., check if service has active appointments
        // if ($service->appointments()->where('status', 'pending')->exists()) {
        //     throw new \Exception('Cannot delete service with pending appointments');
        // }
    }
}
