<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'thumbnail',
        'category',
        'alt_text',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url', 'thumbnail_url'];

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail
            ? asset('storage/' . $this->thumbnail)
            : asset('storage/' . $this->image);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function categories(): array
    {
        return [
            'clinic' => 'Clinic Interior',
            'equipment' => 'Dental Equipment',
            'team' => 'Our Team',
            'before-after' => 'Before & After',
            'patients' => 'Happy Patients',
            'events' => 'Events',
        ];
    }
}
