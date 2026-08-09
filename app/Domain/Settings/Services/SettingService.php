<?php

namespace App\Domain\Settings\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Load ALL settings once, cache indefinitely.
     *
     * Only raw attribute arrays go into the cache, never Setting models.
     * config/cache.php sets 'serializable_classes' => false (Laravel 13's
     * default hardening against gadget chains), so any object round-tripped
     * through the cache comes back as __PHP_Incomplete_Class. Caching
     * primitives and re-hydrating keeps that protection switched on.
     */
    public function all(): Collection
    {
        $rows = Cache::rememberForever(
            Setting::CACHE_KEY,
            fn() => Setting::all()->map->getAttributes()->all()
        );

        // A cache entry written before this fix holds serialized models.
        // Drop it and rebuild rather than handing hydrate() garbage.
        if (!is_array($rows)) {
            Cache::forget(Setting::CACHE_KEY);
            $rows = Setting::all()->map->getAttributes()->all();
            Cache::forever(Setting::CACHE_KEY, $rows);
        }

        return Setting::hydrate($rows)->keyBy('key');
    }

    /**
     * Get a single setting value with proper type casting.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = $this->all()->get($key);

        return $setting?->casted_value ?? $default;
    }

    /**
     * Update or create a setting.
     */
    public function set(string $key, mixed $value): Setting
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        if (!$setting->exists) {
            $setting->group = 'general';
            $setting->type  = $this->inferType($value);
            $setting->label = ucwords(str_replace('_', ' ', $key));
        }

        if (!$setting->is_editable && $setting->exists) {
            throw new \DomainException("Setting [{$key}] is locked and cannot be modified.");
        }

        $setting->value = $value;
        $setting->save();

        return $setting;
    }

    /**
     * Bulk update settings.
     *
     * @param array<string, mixed> $items
     */
    public function bulkSet(array $items): Collection
    {
        $updated = collect();

        foreach ($items as $key => $value) {
            $updated->push($this->set($key, $value));
        }

        return $updated;
    }

    /**
     * Only settings marked public (safe for frontend).
     */
    public function publicSettings(): array
    {
        return $this->all()
            ->filter(fn(Setting $s) => $s->is_public)
            ->mapWithKeys(fn(Setting $s) => [$s->key => $s->casted_value])
            ->toArray();
    }

    /**
     * Auto-detect the type based on the PHP value.
     */
    private function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value)    => 'boolean',
            is_int($value)     => 'integer',
            is_float($value)   => 'float',
            is_array($value)   => 'json',
            default            => 'string',
        };
    }
}