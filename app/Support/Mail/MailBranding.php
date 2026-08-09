<?php

namespace App\Support\Mail;

use Illuminate\Support\Str;

/**
 * Clinic branding for outgoing email.
 *
 * Blade templates call these statics directly rather than relying on injected
 * view data. Passport's own password-reset mail renders through the same theme
 * and header, and it passes none of our variables -- statics are the only
 * things guaranteed to be available in every render.
 *
 * Colours/spacing live in resources/views/mail/smile.blade.php. This class is
 * only for content: who the clinic is and how to reach it.
 */
final class MailBranding
{
    public static function clinicName(): string
    {
        return (string) (setting('clinic_name') ?: config('app.name', 'Smile Concept'));
    }

    /**
     * Absolute, publicly reachable logo URL -- or null to render a wordmark.
     *
     * Gmail fetches images through googleusercontent.com, so it can never load
     * a relative path or anything on localhost, and it does not render data:
     * URIs at all. The seeded value is '/logo.png', which would show as a
     * broken image in every inbox. Returning null instead lets the header fall
     * back to styled text, which always looks intentional.
     */
    public static function logoUrl(): ?string
    {
        $url = trim((string) setting('logo_url', ''));

        if (!Str::startsWith($url, ['http://', 'https://'])) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return null;
        }

        return $url;
    }

    public static function primaryColor(): string
    {
        return (string) (setting('primary_color') ?: '#2563eb');
    }

    public static function phone(): ?string
    {
        return self::nullableSetting('clinic_phone');
    }

    public static function email(): ?string
    {
        return self::nullableSetting('clinic_email');
    }

    public static function address(): ?string
    {
        return self::nullableSetting('clinic_address');
    }

    /**
     * Where "visit our site" should point. Prefers the Flutter client, since
     * this API serves no patient-facing pages.
     */
    public static function siteUrl(): string
    {
        return rtrim((string) (config('app.frontend_url') ?: config('app.url')), '/');
    }

    /**
     * @return array<string, string|null>
     */
    public static function toArray(): array
    {
        return [
            'clinicName'    => self::clinicName(),
            'clinicPhone'   => self::phone(),
            'clinicEmail'   => self::email(),
            'clinicAddress' => self::address(),
            'clinicSiteUrl' => self::siteUrl(),
        ];
    }

    private static function nullableSetting(string $key): ?string
    {
        $value = trim((string) setting($key, ''));

        return $value === '' ? null : $value;
    }
}
