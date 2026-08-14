<?php

namespace App\Domain\Settings\DTOs;

use App\Domain\Settings\Services\SettingService;
use Carbon\Carbon;

/**
 * Typed read model over the appointment-related rows of the `settings` table.
 *
 * The settings table is untyped key-value storage, so every property here
 * carries a DEFAULT_* constant. A missing or malformed row falls back to its
 * default rather than fataling the booking path.
 */
class AppointmentSettings
{
    public const DEFAULT_SLOT_DURATION          = 30;
    public const DEFAULT_BUFFER_MINUTES         = 0;
    public const DEFAULT_OPENS_AT               = '09:00';
    public const DEFAULT_CLOSES_AT              = '18:00';
    public const DEFAULT_LUNCH_START            = '12:00';
    public const DEFAULT_LUNCH_END              = '13:00';
    public const DEFAULT_WORKING_DAYS           = [1, 2, 3, 4, 5, 6];
    public const DEFAULT_MAX_PER_DENTIST_PER_DAY = 12;
    public const DEFAULT_MAX_PER_DAY            = 60;
    public const DEFAULT_MAX_CONCURRENT         = 3;
    public const DEFAULT_LEAD_TIME_HOURS        = 2;
    public const DEFAULT_MAX_ADVANCE_DAYS       = 90;
    public const DEFAULT_MAX_FUTURE_PER_PATIENT = 3;
    public const DEFAULT_CANCELLATION_HOURS     = 24;
    public const DEFAULT_REMINDER_OFFSETS       = [24, 1];
    public const DEFAULT_REMINDER_CHANNELS      = ['email', 'sms'];
    public const DEFAULT_FOLLOWUP_HOURS_AFTER   = 24;
    public const DEFAULT_WAITLIST_WINDOW_MINUTES = 120;

    /**
     * @param array<int, int>    $workingDays     Day numbers, Sunday = 0 (matches Carbon::dayOfWeek).
     * @param array<int, int>    $reminderOffsets Hours before start, descending: [first, second].
     * @param array<int, string> $reminderChannels
     */
    public function __construct(
        public readonly int    $slotDurationMinutes,
        public readonly int    $bufferMinutes,
        public readonly string $opensAt,
        public readonly string $closesAt,
        public readonly ?string $lunchStart,
        public readonly ?string $lunchEnd,
        public readonly array  $workingDays,
        public readonly int    $maxPerDentistPerDay,
        public readonly int    $maxPerDay,
        public readonly int    $maxConcurrent,
        public readonly int    $leadTimeHours,
        public readonly int    $maxAdvanceDays,
        public readonly bool   $allowSameDayBooking,
        public readonly int    $maxFuturePerPatient,
        public readonly bool   $allowOnlineBooking,
        public readonly int    $cancellationWindowHours,
        public readonly float  $lateCancellationFee,
        public readonly float  $noShowFee,
        public readonly int    $noShowsBeforeBlock,
        public readonly array  $reminderOffsets,
        public readonly array  $reminderChannels,
        public readonly bool   $sendBookingConfirmationEmail,
        public readonly bool   $sendCancellationEmail,
        public readonly bool   $sendFollowUpEmail,
        public readonly int    $followUpHoursAfter,
        public readonly bool   $enableWaitlist,
        public readonly int    $waitlistOfferWindowMinutes,
    ) {}

    public static function make(SettingService $settings): self
    {
        return new self(
            slotDurationMinutes: self::intOf($settings, 'appointment_slot_duration', self::DEFAULT_SLOT_DURATION),
            bufferMinutes: self::intOf($settings, 'appointment_buffer_minutes', self::DEFAULT_BUFFER_MINUTES, min: 0),
            opensAt: self::timeOf($settings, 'clinic_opens_at', self::DEFAULT_OPENS_AT),
            closesAt: self::timeOf($settings, 'clinic_closes_at', self::DEFAULT_CLOSES_AT),
            lunchStart: self::nullableTimeOf($settings, 'lunch_break_start'),
            lunchEnd: self::nullableTimeOf($settings, 'lunch_break_end'),
            workingDays: self::workingDaysOf($settings),
            maxPerDentistPerDay: self::intOf($settings, 'max_appointments_per_dentist_per_day', self::DEFAULT_MAX_PER_DENTIST_PER_DAY),
            maxPerDay: self::intOf($settings, 'max_appointments_per_day', self::DEFAULT_MAX_PER_DAY),
            maxConcurrent: self::intOf($settings, 'max_concurrent_appointments', self::DEFAULT_MAX_CONCURRENT),
            leadTimeHours: self::intOf($settings, 'booking_lead_time_hours', self::DEFAULT_LEAD_TIME_HOURS, min: 0),
            maxAdvanceDays: self::intOf($settings, 'max_advance_booking_days', self::DEFAULT_MAX_ADVANCE_DAYS),
            allowSameDayBooking: (bool) $settings->get('allow_same_day_booking', true),
            maxFuturePerPatient: self::intOf($settings, 'max_future_appointments_per_patient', self::DEFAULT_MAX_FUTURE_PER_PATIENT),
            allowOnlineBooking: (bool) $settings->get('allow_online_booking', true),
            cancellationWindowHours: self::intOf($settings, 'cancellation_window_hours', self::DEFAULT_CANCELLATION_HOURS, min: 0),
            lateCancellationFee: (float) $settings->get('late_cancellation_fee', 0),
            noShowFee: (float) $settings->get('no_show_fee', 0),
            noShowsBeforeBlock: self::intOf($settings, 'no_shows_before_block', 3),
            reminderOffsets: self::reminderOffsetsOf($settings),
            reminderChannels: self::reminderChannelsOf($settings),
            sendBookingConfirmationEmail: (bool) $settings->get('send_booking_confirmation_email', true),
            sendCancellationEmail: (bool) $settings->get('send_cancellation_email', true),
            sendFollowUpEmail: (bool) $settings->get('send_followup_email', false),
            followUpHoursAfter: self::intOf($settings, 'followup_email_hours_after', self::DEFAULT_FOLLOWUP_HOURS_AFTER),
            enableWaitlist: (bool) $settings->get('enable_waitlist', false),
            waitlistOfferWindowMinutes: self::intOf($settings, 'waitlist_offer_window_minutes', self::DEFAULT_WAITLIST_WINDOW_MINUTES),
        );
    }

    // ─── Derived helpers ──────────────────────────────

    /**
     * Distance between the start of one slot and the start of the next.
     */
    public function slotStrideMinutes(): int
    {
        return $this->slotDurationMinutes + $this->bufferMinutes;
    }

    public function isWorkingDay(Carbon $date): bool
    {
        return in_array($date->dayOfWeek, $this->workingDays, true);
    }

    /**
     * Clinic open/close instants on a given calendar date.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    public function clinicWindowFor(Carbon $date): array
    {
        return [
            $date->copy()->setTimeFromTimeString($this->opensAt),
            $date->copy()->setTimeFromTimeString($this->closesAt),
        ];
    }

    /**
     * Lunch break instants on a given calendar date, or null when no break is configured.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public function lunchWindowFor(Carbon $date): ?array
    {
        if ($this->lunchStart === null || $this->lunchEnd === null) {
            return null;
        }

        $start = $date->copy()->setTimeFromTimeString($this->lunchStart);
        $end   = $date->copy()->setTimeFromTimeString($this->lunchEnd);

        return $end->gt($start) ? [$start, $end] : null;
    }

    public function overlapsLunch(Carbon $start, Carbon $end): bool
    {
        $lunch = $this->lunchWindowFor($start);

        if ($lunch === null) {
            return false;
        }

        [$lunchStart, $lunchEnd] = $lunch;

        return $start->lt($lunchEnd) && $end->gt($lunchStart);
    }

    /**
     * Earliest instant a patient may book, honouring the lead time and the
     * same-day toggle.
     */
    public function earliestBookableAt(): Carbon
    {
        $earliest = Carbon::now()->addHours($this->leadTimeHours);

        if (!$this->allowSameDayBooking) {
            $earliest = Carbon::tomorrow()->max($earliest);
        }

        return $earliest;
    }

    public function latestBookableAt(): Carbon
    {
        return Carbon::now()->addDays($this->maxAdvanceDays)->endOfDay();
    }

    /**
     * Hours before the appointment at which each reminder fires.
     *
     * @return array<int, int>
     */
    public function firstAndSecondReminderHours(): array
    {
        return $this->reminderOffsets;
    }

    // ─── Coercion ─────────────────────────────────────

    private static function intOf(SettingService $settings, string $key, int $default, int $min = 1): int
    {
        $value = $settings->get($key, $default);

        if (!is_numeric($value)) {
            return $default;
        }

        return max($min, (int) $value);
    }

    /**
     * Settings store times as free text. Anything Carbon cannot parse falls
     * back to the default rather than throwing inside the booking path.
     */
    private static function timeOf(SettingService $settings, string $key, string $default): string
    {
        return self::normalizeTime($settings->get($key, $default)) ?? $default;
    }

    private static function nullableTimeOf(SettingService $settings, string $key): ?string
    {
        return self::normalizeTime($settings->get($key));
    }

    private static function normalizeTime(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::createFromTimeString(trim($value))->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, int>
     */
    private static function workingDaysOf(SettingService $settings): array
    {
        $raw = $settings->get('working_days', self::DEFAULT_WORKING_DAYS);

        if (!is_array($raw)) {
            return self::DEFAULT_WORKING_DAYS;
        }

        $days = collect($raw)
            ->filter(fn($d) => is_numeric($d))
            ->map(fn($d) => (int) $d)
            ->filter(fn(int $d) => $d >= 0 && $d <= 6)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $days === [] ? self::DEFAULT_WORKING_DAYS : $days;
    }

    /**
     * @return array<int, int>
     */
    private static function reminderOffsetsOf(SettingService $settings): array
    {
        $raw = $settings->get('reminder_offsets', self::DEFAULT_REMINDER_OFFSETS);

        if (!is_array($raw)) {
            return self::DEFAULT_REMINDER_OFFSETS;
        }

        $offsets = collect($raw)
            ->filter(fn($h) => is_numeric($h) && (int) $h > 0)
            ->map(fn($h) => (int) $h)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        return $offsets === [] ? self::DEFAULT_REMINDER_OFFSETS : $offsets;
    }

    /**
     * @return array<int, string>
     */
    private static function reminderChannelsOf(SettingService $settings): array
    {
        $raw = $settings->get('reminder_channels', self::DEFAULT_REMINDER_CHANNELS);

        if (!is_array($raw)) {
            return self::DEFAULT_REMINDER_CHANNELS;
        }

        $allowed  = ['email', 'sms', 'push', 'in_app'];
        $channels = collect($raw)
            ->filter(fn($c) => is_string($c) && in_array($c, $allowed, true))
            ->unique()
            ->values()
            ->all();

        return $channels === [] ? self::DEFAULT_REMINDER_CHANNELS : $channels;
    }
}
