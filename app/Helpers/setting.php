<?php

use App\Domain\Settings\Services\SettingService;

if (!function_exists('setting')) {
    /**
     * Global helper to read a setting.
     *
     *  setting('tax_rate', 0);
     *  setting('sms_enabled', false);
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingService::class)->get($key, $default);
    }
}