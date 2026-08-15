<?php

namespace App\Domain\Inventories\DTOs;

use App\Domain\Settings\Services\SettingService;

/**
 * Typed read model over the inventory rows of the `settings` table.
 *
 * Same shape as AppointmentSettings: the settings table is untyped key-value
 * storage, so every property carries a DEFAULT_* constant and a malformed row
 * falls back to it rather than fataling — here, on the appointment-completion
 * path, where a bad settings row must never stop a clinic checking a patient out.
 */
class InventorySettings
{
    public const DEFAULT_AUTO_DEDUCT           = true;
    public const DEFAULT_ALLOW_NEGATIVE_STOCK  = true;
    public const DEFAULT_TRACK_EXPIRY          = true;
    public const DEFAULT_EXPIRY_WARNING_DAYS   = 30;
    public const DEFAULT_MINIMUM_THRESHOLD     = 10;
    public const DEFAULT_LOW_STOCK_ALERTS      = true;
    public const DEFAULT_LOW_STOCK_ALERT_HOUR  = 8;
    public const DEFAULT_LOW_STOCK_COOLDOWN    = 3;

    public function __construct(
        /** Deduct recipe supplies when an appointment is completed. */
        public readonly bool $autoDeductEnabled,

        /**
         * Whether a shortfall is recorded quietly or flagged.
         *
         * Note what this does NOT do: it never causes a rejection. Automatic
         * deduction is not allowed to block clinical work, so running out of
         * gloves is always recorded, never refused.
         */
        public readonly bool $allowNegativeStock,

        public readonly bool $trackExpiry,
        public readonly int  $expiryWarningDays,
        public readonly int  $defaultMinimumThreshold,
        public readonly bool $lowStockAlertsEnabled,
        public readonly int  $lowStockAlertHour,
        public readonly int  $lowStockCooldownDays,
    ) {}

    public static function make(SettingService $settings): self
    {
        return new self(
            autoDeductEnabled: self::boolOf($settings, 'inventory_auto_deduct_enabled', self::DEFAULT_AUTO_DEDUCT),
            allowNegativeStock: self::boolOf($settings, 'inventory_allow_negative_stock', self::DEFAULT_ALLOW_NEGATIVE_STOCK),
            trackExpiry: self::boolOf($settings, 'inventory_track_expiry', self::DEFAULT_TRACK_EXPIRY),
            expiryWarningDays: self::intOf($settings, 'inventory_expiry_warning_days', self::DEFAULT_EXPIRY_WARNING_DAYS),
            defaultMinimumThreshold: self::intOf($settings, 'inventory_default_minimum_threshold', self::DEFAULT_MINIMUM_THRESHOLD, min: 0),
            lowStockAlertsEnabled: self::boolOf($settings, 'inventory_low_stock_alert_enabled', self::DEFAULT_LOW_STOCK_ALERTS),
            lowStockAlertHour: self::hourOf($settings, 'inventory_low_stock_alert_hour', self::DEFAULT_LOW_STOCK_ALERT_HOUR),
            lowStockCooldownDays: self::intOf($settings, 'inventory_low_stock_cooldown_days', self::DEFAULT_LOW_STOCK_COOLDOWN, min: 0),
        );
    }

    // ─── Coercion ─────────────────────────────────────

    private static function boolOf(SettingService $settings, string $key, bool $default): bool
    {
        $value = $settings->get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }

    private static function intOf(SettingService $settings, string $key, int $default, int $min = 1): int
    {
        $value = $settings->get($key, $default);

        if (!is_numeric($value)) {
            return $default;
        }

        return max($min, (int) $value);
    }

    /** Clamped to a real clock hour so the alert command cannot silently never fire. */
    private static function hourOf(SettingService $settings, string $key, int $default): int
    {
        $value = $settings->get($key, $default);

        if (!is_numeric($value)) {
            return $default;
        }

        $hour = (int) $value;

        return ($hour >= 0 && $hour <= 23) ? $hour : $default;
    }
}
