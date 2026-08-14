<?php

namespace App\Http\Requests\v1\AppointmentSetting;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Atomic save of the whole appointment-settings form.
 *
 * Every key is required so a save is all-or-nothing; cross-field rules live
 * in withValidator() so the client gets proper 422 field errors.
 */
class UpdateAppointmentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.update');
    }

    public function rules(): array
    {
        return [
            // Scheduling
            'appointment_slot_duration'  => ['required', 'integer', 'min:5', 'max:240'],
            'appointment_buffer_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'clinic_opens_at'            => ['required', 'date_format:H:i'],
            'clinic_closes_at'           => ['required', 'date_format:H:i', 'after:clinic_opens_at'],
            'lunch_break_start'          => ['required', 'date_format:H:i'],
            'lunch_break_end'            => ['required', 'date_format:H:i', 'after:lunch_break_start'],
            'working_days'               => ['required', 'array', 'min:1'],
            'working_days.*'             => ['integer', 'between:0,6', 'distinct'],

            // Capacity
            'max_appointments_per_dentist_per_day' => ['required', 'integer', 'min:1', 'max:200'],
            'max_appointments_per_day'             => ['required', 'integer', 'min:1', 'max:2000'],
            'max_concurrent_appointments'          => ['required', 'integer', 'min:1', 'max:100'],

            // Booking window
            'booking_lead_time_hours'             => ['required', 'integer', 'min:0', 'max:720'],
            'max_advance_booking_days'            => ['required', 'integer', 'min:1', 'max:730'],
            'allow_same_day_booking'              => ['required', 'boolean'],
            'max_future_appointments_per_patient' => ['required', 'integer', 'min:1', 'max:50'],
            'allow_online_booking'                => ['required', 'boolean'],

            // Cancellation & no-show
            'cancellation_window_hours' => ['required', 'integer', 'min:0', 'max:720'],
            'late_cancellation_fee'     => ['required', 'numeric', 'min:0', 'max:1000000'],
            'no_show_fee'               => ['required', 'numeric', 'min:0', 'max:1000000'],
            'no_shows_before_block'     => ['required', 'integer', 'min:1', 'max:50'],

            // Reminders & email — offsets are [first, second] hours before start
            'reminder_offsets'                => ['required', 'array', 'size:2'],
            'reminder_offsets.*'              => ['integer', 'min:1', 'max:720'],
            'send_booking_confirmation_email' => ['required', 'boolean'],
            'send_cancellation_email'         => ['required', 'boolean'],
            'send_followup_email'             => ['required', 'boolean'],
            'followup_email_hours_after'      => ['required', 'integer', 'min:1', 'max:720'],

            // Waitlist (stored, not yet enforced)
            'enable_waitlist'               => ['required', 'boolean'],
            'waitlist_offer_window_minutes' => ['required', 'integer', 'min:5', 'max:10080'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $data = $v->getData();

            $opens      = $data['clinic_opens_at'] ?? null;
            $closes     = $data['clinic_closes_at'] ?? null;
            $lunchStart = $data['lunch_break_start'] ?? null;
            $lunchEnd   = $data['lunch_break_end'] ?? null;

            // Lunch must sit inside clinic hours. String compare is safe:
            // date_format:H:i has already run, and zero-padded H:i sorts
            // lexicographically in time order.
            if ($opens && $lunchStart && $lunchStart < $opens) {
                $v->errors()->add('lunch_break_start', 'Lunch break cannot start before the clinic opens.');
            }
            if ($closes && $lunchEnd && $lunchEnd > $closes) {
                $v->errors()->add('lunch_break_end', 'Lunch break cannot end after the clinic closes.');
            }

            // Slot must physically fit in the morning or afternoon block.
            $slot = (int) ($data['appointment_slot_duration'] ?? 0);
            if ($opens && $closes && $slot > 0) {
                $window = $this->minutesBetween($opens, $closes);
                if ($slot > $window) {
                    $v->errors()->add(
                        'appointment_slot_duration',
                        "A {$slot}-minute slot does not fit inside clinic hours ({$window} minutes)."
                    );
                }
            }

            // Second reminder must fire closer to the appointment than the first.
            $offsets = $data['reminder_offsets'] ?? null;
            if (is_array($offsets) && count($offsets) === 2
                && is_numeric($offsets[0]) && is_numeric($offsets[1])
                && (int) $offsets[1] >= (int) $offsets[0]
            ) {
                $v->errors()->add(
                    'reminder_offsets',
                    'The second reminder must be closer to the appointment than the first '
                    . '(e.g. first 24 hours before, second 1 hour before).'
                );
            }
        });
    }

    private function minutesBetween(string $from, string $to): int
    {
        [$fh, $fm] = array_map('intval', explode(':', $from));
        [$th, $tm] = array_map('intval', explode(':', $to));

        return ($th * 60 + $tm) - ($fh * 60 + $fm);
    }
}
