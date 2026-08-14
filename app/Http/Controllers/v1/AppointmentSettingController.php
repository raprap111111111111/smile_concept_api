<?php

namespace App\Http\Controllers\v1;

use App\Domain\Settings\DTOs\AppointmentSettings;
use App\Domain\Settings\Services\SettingService;
use App\Events\Settings\AppointmentSettingsUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\AppointmentSetting\GetAppointmentSettingsRequest;
use App\Http\Requests\v1\AppointmentSetting\UpdateAppointmentSettingsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * One typed endpoint for the whole appointment-settings form.
 *
 * Values live in the generic `settings` table; this controller reads them
 * through the AppointmentSettings facade (so GET always shows exactly what
 * enforcement uses, defaults included) and writes them via
 * SettingService::bulkSet inside a transaction (so a save is all-or-nothing).
 */
class AppointmentSettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function show(GetAppointmentSettingsRequest $request): JsonResponse
    {
        return $this->successResponse(
            $this->toPayload(app(AppointmentSettings::class)),
            'Appointment settings retrieved.'
        );
    }

    public function update(UpdateAppointmentSettingsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Cast explicitly rather than trusting request types: booleans arrive
        // as true/"1"/1 depending on client, and SettingService infers types
        // from PHP values when a row is missing.
        $payload = [
            'appointment_slot_duration'  => (int) $validated['appointment_slot_duration'],
            'appointment_buffer_minutes' => (int) $validated['appointment_buffer_minutes'],
            'clinic_opens_at'            => (string) $validated['clinic_opens_at'],
            'clinic_closes_at'           => (string) $validated['clinic_closes_at'],
            'lunch_break_start'          => (string) $validated['lunch_break_start'],
            'lunch_break_end'            => (string) $validated['lunch_break_end'],
            'working_days'               => array_values(array_map('intval', $validated['working_days'])),

            'max_appointments_per_dentist_per_day' => (int) $validated['max_appointments_per_dentist_per_day'],
            'max_appointments_per_day'             => (int) $validated['max_appointments_per_day'],
            'max_concurrent_appointments'          => (int) $validated['max_concurrent_appointments'],

            'booking_lead_time_hours'             => (int) $validated['booking_lead_time_hours'],
            'max_advance_booking_days'            => (int) $validated['max_advance_booking_days'],
            'allow_same_day_booking'              => filter_var($validated['allow_same_day_booking'], FILTER_VALIDATE_BOOLEAN),
            'max_future_appointments_per_patient' => (int) $validated['max_future_appointments_per_patient'],
            'allow_online_booking'                => filter_var($validated['allow_online_booking'], FILTER_VALIDATE_BOOLEAN),

            'cancellation_window_hours' => (int) $validated['cancellation_window_hours'],
            'late_cancellation_fee'     => (float) $validated['late_cancellation_fee'],
            'no_show_fee'               => (float) $validated['no_show_fee'],
            'no_shows_before_block'     => (int) $validated['no_shows_before_block'],

            'reminder_offsets'                => array_values(array_map('intval', $validated['reminder_offsets'])),
            'send_booking_confirmation_email' => filter_var($validated['send_booking_confirmation_email'], FILTER_VALIDATE_BOOLEAN),
            'send_cancellation_email'         => filter_var($validated['send_cancellation_email'], FILTER_VALIDATE_BOOLEAN),
            'send_followup_email'             => filter_var($validated['send_followup_email'], FILTER_VALIDATE_BOOLEAN),
            'followup_email_hours_after'      => (int) $validated['followup_email_hours_after'],

            'enable_waitlist'               => filter_var($validated['enable_waitlist'], FILTER_VALIDATE_BOOLEAN),
            'waitlist_offer_window_minutes' => (int) $validated['waitlist_offer_window_minutes'],
        ];

        DB::transaction(fn() => $this->settings->bulkSet($payload));

        // Rebuild from storage, not from $payload: the response must prove the
        // round-trip (write, cache bust, re-read) actually worked.
        $fresh = $this->toPayload(AppointmentSettings::make($this->settings));

        AppointmentSettingsUpdated::dispatch($fresh);

        return $this->successResponse($fresh, 'Appointment settings updated.');
    }

    /**
     * Flat key => value map, keyed by the settings-table keys the docs and
     * error messages use.
     *
     * @return array<string, mixed>
     */
    private function toPayload(AppointmentSettings $s): array
    {
        return [
            'appointment_slot_duration'  => $s->slotDurationMinutes,
            'appointment_buffer_minutes' => $s->bufferMinutes,
            'clinic_opens_at'            => $s->opensAt,
            'clinic_closes_at'           => $s->closesAt,
            'lunch_break_start'          => $s->lunchStart,
            'lunch_break_end'            => $s->lunchEnd,
            'working_days'               => $s->workingDays,

            'max_appointments_per_dentist_per_day' => $s->maxPerDentistPerDay,
            'max_appointments_per_day'             => $s->maxPerDay,
            'max_concurrent_appointments'          => $s->maxConcurrent,

            'booking_lead_time_hours'             => $s->leadTimeHours,
            'max_advance_booking_days'            => $s->maxAdvanceDays,
            'allow_same_day_booking'              => $s->allowSameDayBooking,
            'max_future_appointments_per_patient' => $s->maxFuturePerPatient,
            'allow_online_booking'                => $s->allowOnlineBooking,

            'cancellation_window_hours' => $s->cancellationWindowHours,
            'late_cancellation_fee'     => $s->lateCancellationFee,
            'no_show_fee'               => $s->noShowFee,
            'no_shows_before_block'     => $s->noShowsBeforeBlock,

            'reminder_offsets'                => $s->reminderOffsets,
            'send_booking_confirmation_email' => $s->sendBookingConfirmationEmail,
            'send_cancellation_email'         => $s->sendCancellationEmail,
            'send_followup_email'             => $s->sendFollowUpEmail,
            'followup_email_hours_after'      => $s->followUpHoursAfter,

            'enable_waitlist'               => $s->enableWaitlist,
            'waitlist_offer_window_minutes' => $s->waitlistOfferWindowMinutes,
        ];
    }
}
