<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_description',
        'icon',
        'image',
        'price',
        'price_max',
        'duration_minutes',
        'category',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_max' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'duration_minutes' => 'integer',
    ];

    protected $appends = ['image_url', 'price_range'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function getPriceRangeAttribute(): ?string
    {
        if (!$this->price) {
            return null;
        }

        if ($this->price_max && $this->price_max > $this->price) {
            return '₱' . number_format($this->price, 0) . ' - ₱' . number_format($this->price_max, 0);
        }

        return '₱' . number_format($this->price, 0);
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
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
