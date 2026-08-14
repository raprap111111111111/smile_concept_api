<?php

namespace App\Domain\Appointments\Services;

use App\Domain\Appointments\DTOs\CreateAppointmentDTO;
use App\Domain\Settings\DTOs\AppointmentSettings;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Every configurable booking rule, checked in one place.
 *
 * Two tiers:
 *  - HARD rules bind everyone, staff included: working days, clinic hours,
 *    lunch break. (Dentist double-booking is also hard, but stays with
 *    AppointmentRepository::checkConflicts() where it already lives.)
 *  - SOFT rules bind patients only: capacity caps, booking windows, the
 *    same-day toggle, per-patient future limit, cancellation cutoff, and the
 *    online-booking master switch. Staff bypass these so the front desk can
 *    squeeze in walk-ins and emergencies.
 *
 * Every rejection message names the setting key that produced it, so a
 * mysterious 422 can be traced to a settings row without opening the code.
 */
class BookingRuleService
{
    /**
     * Statuses that occupy capacity. Mirrors checkConflicts(), which excludes
     * cancelled and completed.
     */
    private const BLOCKING_STATUSES = ['pending', 'confirmed'];

    public function __construct(
        private readonly AppointmentSettings $settings,
    ) {}

    public function assertCanBook(CreateAppointmentDTO $dto, bool $isStaff): void
    {
        $start = Carbon::parse($dto->startTime);
        $end   = Carbon::parse($dto->endTime);

        $this->assertHardRules($start, $end);

        if ($isStaff) {
            return;
        }

        $this->assertAllowOnlineBooking();
        $this->assertBookingWindow($start);
        $this->assertDentistDailyCap($dto->doctorId, $start);
        $this->assertClinicDailyCap($start);
        $this->assertConcurrentCap($start, $end);

        if ($dto->userId !== null) {
            $this->assertPatientFutureCap($dto->userId);
        }
    }

    public function assertCanReschedule(
        Appointment $appointment,
        int         $doctorId,
        string      $startTime,
        string      $endTime,
        bool        $isStaff,
    ): void {
        $start = Carbon::parse($startTime);
        $end   = Carbon::parse($endTime);

        $this->assertHardRules($start, $end);

        if ($isStaff) {
            return;
        }

        $this->assertBookingWindow($start);
        $this->assertDentistDailyCap($doctorId, $start, excludeId: $appointment->id);
        $this->assertClinicDailyCap($start, excludeId: $appointment->id);
        $this->assertConcurrentCap($start, $end, excludeId: $appointment->id);
    }

    public function assertCanCancel(Appointment $appointment, bool $isStaff): void
    {
        if ($isStaff) {
            return;
        }

        $cutoffHours = $this->settings->cancellationWindowHours;

        if ($cutoffHours <= 0) {
            return;
        }

        $deadline = Carbon::parse($appointment->start_time)->subHours($cutoffHours);

        if (Carbon::now()->gt($deadline)) {
            throw ValidationException::withMessages([
                'status' => [
                    "Appointments must be cancelled at least {$cutoffHours} hours in advance; "
                    . "the deadline for this one was {$deadline->format('M j, Y g:i A')} "
                    . "(cancellation_window_hours = {$cutoffHours}). Please contact the clinic.",
                ],
            ]);
        }
    }

    // ─── Hard rules (everyone) ────────────────────────

    private function assertHardRules(Carbon $start, Carbon $end): void
    {
        $this->assertWorkingDay($start);
        $this->assertWithinClinicHours($start, $end);
        $this->assertOutsideLunchBreak($start, $end);
    }

    private function assertWorkingDay(Carbon $start): void
    {
        if ($this->settings->isWorkingDay($start)) {
            return;
        }

        $this->reject(
            "The clinic does not accept appointments on {$start->format('l')}s (working_days)."
        );
    }

    private function assertWithinClinicHours(Carbon $start, Carbon $end): void
    {
        [$opensAt, $closesAt] = $this->settings->clinicWindowFor($start);

        if ($start->gte($opensAt) && $end->lte($closesAt)) {
            return;
        }

        $this->reject(
            "Appointments must fall between {$this->settings->opensAt} and {$this->settings->closesAt} "
            . "(clinic_opens_at / clinic_closes_at)."
        );
    }

    private function assertOutsideLunchBreak(Carbon $start, Carbon $end): void
    {
        if (!$this->settings->overlapsLunch($start, $end)) {
            return;
        }

        $this->reject(
            "This time overlaps the clinic lunch break, {$this->settings->lunchStart} to "
            . "{$this->settings->lunchEnd} (lunch_break_start / lunch_break_end)."
        );
    }

    // ─── Soft rules (patients only) ───────────────────

    private function assertAllowOnlineBooking(): void
    {
        if ($this->settings->allowOnlineBooking) {
            return;
        }

        $this->reject(
            'Online booking is currently disabled; please call the clinic to book (allow_online_booking).'
        );
    }

    private function assertBookingWindow(Carbon $start): void
    {
        if (!$this->settings->allowSameDayBooking && $start->isToday()) {
            $this->reject(
                'Same-day booking is not available; please choose a later date (allow_same_day_booking).'
            );
        }

        $earliest = $this->settings->earliestBookableAt();

        if ($start->lt($earliest)) {
            $hours = $this->settings->leadTimeHours;
            $this->reject(
                "Appointments must be booked at least {$hours} hours in advance "
                . "(booking_lead_time_hours = {$hours})."
            );
        }

        $latest = $this->settings->latestBookableAt();

        if ($start->gt($latest)) {
            $days = $this->settings->maxAdvanceDays;
            $this->reject(
                "Appointments can be booked at most {$days} days ahead "
                . "(max_advance_booking_days = {$days})."
            );
        }
    }

    private function assertDentistDailyCap(int $doctorId, Carbon $start, ?int $excludeId = null): void
    {
        $cap = $this->settings->maxPerDentistPerDay;

        $count = $this->blockingQuery($excludeId)
            ->where('doctor_id', $doctorId)
            ->whereDate('start_time', $start->toDateString())
            ->count();

        if ($count >= $cap) {
            $this->reject(
                "This dentist is fully booked on {$start->toDateString()} "
                . "(max_appointments_per_dentist_per_day = {$cap})."
            );
        }
    }

    private function assertClinicDailyCap(Carbon $start, ?int $excludeId = null): void
    {
        $cap = $this->settings->maxPerDay;

        $count = $this->blockingQuery($excludeId)
            ->whereDate('start_time', $start->toDateString())
            ->count();

        if ($count >= $cap) {
            $this->reject(
                "The clinic is fully booked on {$start->toDateString()} "
                . "(max_appointments_per_day = {$cap})."
            );
        }
    }

    private function assertConcurrentCap(Carbon $start, Carbon $end, ?int $excludeId = null): void
    {
        $cap = $this->settings->maxConcurrent;

        $count = $this->blockingQuery($excludeId)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->count();

        if ($count >= $cap) {
            $this->reject(
                "All chairs are occupied during this time slot "
                . "(max_concurrent_appointments = {$cap})."
            );
        }
    }

    private function assertPatientFutureCap(int $userId): void
    {
        $cap = $this->settings->maxFuturePerPatient;

        $count = $this->blockingQuery()
            ->where('user_id', $userId)
            ->where('start_time', '>', Carbon::now())
            ->count();

        if ($count >= $cap) {
            $this->reject(
                "You already have {$count} upcoming appointments; the limit is {$cap} "
                . "(max_future_appointments_per_patient = {$cap})."
            );
        }
    }

    // ─── Plumbing ─────────────────────────────────────

    private function blockingQuery(?int $excludeId = null)
    {
        $query = Appointment::query()->whereIn('status', self::BLOCKING_STATUSES);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    /**
     * @throws ValidationException
     */
    private function reject(string $message): never
    {
        throw ValidationException::withMessages([
            'start_time' => [$message],
        ]);
    }
}
