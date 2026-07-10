<?php

namespace App\Domain\Galleries\DTOs;

use Illuminate\Http\UploadedFile;

class GalleryData
{
    public function __construct(
        public readonly ?UploadedFile $image,
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly string $category = 'clinic',
        public readonly ?string $alt_text = null,
        public readonly bool $is_featured = false,
        public readonly bool $is_active = true,
        public readonly int $sort_order = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            image: $data['image'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? 'clinic',
            alt_text: $data['alt_text'] ?? null,
            is_featured: (bool) ($data['is_featured'] ?? false),
            is_active: (bool) ($data['is_active'] ?? true),
            sort_order: (int) ($data['sort_order'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'alt_text' => $this->alt_text,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ], fn($value) => $value !== null);
    }
}
