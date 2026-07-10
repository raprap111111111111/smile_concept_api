<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'settings.all';

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
        'is_public',
        'is_editable',
    ];

    protected $casts = [
        'is_public'   => 'boolean',
        'is_editable' => 'boolean',
    ];

    /**
     * Cast the raw string value to its proper PHP type.
     */
    public function getCastedValueAttribute(): mixed
    {
        if ($this->value === null) {
            return null;
        }

        return match ($this->type) {
            'integer' => (int) $this->value,
            'float'   => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json'    => json_decode($this->value, true),
            'date'    => \Carbon\Carbon::parse($this->value),
            default   => (string) $this->value,
        };
    }

    /**
     * Serialize the value for storage based on its type.
     */
    public function setValueAttribute($value): void
    {
        $this->attributes['value'] = match ($this->type ?? 'string') {
            'boolean' => $value ? '1' : '0',
            'json'    => is_array($value) ? json_encode($value) : $value,
            default   => is_null($value) ? null : (string) $value,
        };
    }

    // ─── Auto-bust cache on write ─────────────────────
    protected static function booted(): void
    {
        static::saved(fn()   => Cache::forget(self::CACHE_KEY));
        static::deleted(fn() => Cache::forget(self::CACHE_KEY));
    }
}