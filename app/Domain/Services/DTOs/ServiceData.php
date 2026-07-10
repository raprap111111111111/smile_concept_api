<?php

namespace App\Domain\Services\DTOs;

use Illuminate\Http\UploadedFile;

class ServiceData
{
    public function __construct(
        public readonly string $title,
        public readonly string $description,
        public readonly ?string $short_description = null,
        public readonly ?string $icon = null,
        public readonly ?UploadedFile $image = null,
        public readonly ?float $price = null,
        public readonly ?float $price_max = null,
        public readonly ?int $duration_minutes = null,
        public readonly ?string $category = null,
        public readonly bool $is_featured = false,
        public readonly bool $is_active = true,
        public readonly int $sort_order = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'],
            short_description: $data['short_description'] ?? null,
            icon: $data['icon'] ?? null,
            image: $data['image'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            price_max: isset($data['price_max']) ? (float) $data['price_max'] : null,
            duration_minutes: isset($data['duration_minutes']) ? (int) $data['duration_minutes'] : null,
            category: $data['category'] ?? null,
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
            'short_description' => $this->short_description,
            'icon' => $this->icon,
            'price' => $this->price,
            'price_max' => $this->price_max,
            'duration_minutes' => $this->duration_minutes,
            'category' => $this->category,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ], fn($value) => $value !== null);
    }
}
