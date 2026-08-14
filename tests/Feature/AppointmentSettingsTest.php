<?php

namespace Tests\Feature;

use App\Domain\Appointments\DTOs\CreateAppointmentDTO;
use App\Domain\Appointments\Repositories\AppointmentRepository;
use App\Domain\Appointments\Services\BookingRuleService;
use App\Domain\Settings\DTOs\AppointmentSettings;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppointmentSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;
    private Doctor $doctor;
    private User $patient;
    private User $receptionist;

    /** 2026-08-19 is a Wednesday (dayOfWeek = 3). */
    private const NOW = '2026-08-19 08:00:00';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));

        Role::create(['name' => 'patient', 'guard_name' => 'api']);
        Role::create(['name' => 'receptionist', 'guard_name' => 'api']);
        Permission::create(['name' => 'setting.view', 'guard_name' => 'api']);
        Permission::create(['name' => 'setting.update', 'guard_name' => 'api']);

        $this->branch = Branch::create([
            'name'        => 'Main',
            'branch_code' => 'MAIN',
            'address'     => '123 Test St',
        ]);

        $doctorUser = User::factory()->create();
        $this->doctor = Doctor::create([
            'user_id'        => $doctorUser->id,
            'license_number' => 'LIC-001',
        ]);

        DoctorSchedule::create([
            'doctor_id'   => $this->doctor->id,
            'branch_id'   => $this->branch->id,
            'day_of_week' => 3, // Wednesday
            'start_time'  => '09:00:00',
            'end_time'    => '18:00:00',
        ]);

        $this->patient = User::factory()->create();
        $this->patient->assignRole('patient');

        $this->receptionist = User::factory()->create();
        $this->receptionist->assignRole('receptionist');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── Helpers ──────────────────────────────────────

    private function settings(array $overrides = []): AppointmentSettings
    {
        $defaults = [
            'slotDurationMinutes'          => 30,
            'bufferMinutes'                => 0,
            'opensAt'                      => '09:00',
            'closesAt'                     => '18:00',
            'lunchStart'                   => '12:00',
            'lunchEnd'                     => '13:00',
            'workingDays'                  => [1, 2, 3, 4, 5, 6],
            'maxPerDentistPerDay'          => 12,
            'maxPerDay'                    => 60,
            'maxConcurrent'                => 3,
            'leadTimeHours'                => 2,
            'maxAdvanceDays'               => 90,
            'allowSameDayBooking'          => true,
            'maxFuturePerPatient'          => 3,
            'allowOnlineBooking'           => true,
            'cancellationWindowHours'      => 24,
            'lateCancellationFee'          => 0.0,
            'noShowFee'                    => 0.0,
            'noShowsBeforeBlock'           => 3,
            'reminderOffsets'              => [24, 1],
            'reminderChannels'             => ['email'],
            'sendBookingConfirmationEmail' => true,
            'sendCancellationEmail'        => true,
            'sendFollowUpEmail'            => false,
            'followUpHoursAfter'           => 24,
            'enableWaitlist'               => false,
            'waitlistOfferWindowMinutes'   => 120,
        ];

        return new AppointmentSettings(...array_merge($defaults, $overrides));
    }

    private function rules(array $overrides = []): BookingRuleService
    {
        return new BookingRuleService($this->settings($overrides));
    }

    private function dto(string $start, string $end, ?int $userId = null): CreateAppointmentDTO
    {
        return new CreateAppointmentDTO(
            doctorId: $this->doctor->id,
            branchId: $this->branch->id,
            startTime: $start,
            endTime: $end,
            status: AppointmentStatus::PENDING,
            userId: $userId ?? $this->patient->id,
        );
    }

    private function makeAppointment(string $start, string $end, array $overrides = []): Appointment
    {
        return Appointment::create(array_merge([
            'user_id'    => $this->patient->id,
            'doctor_id'  => $this->doctor->id,
            'branch_id'  => $this->branch->id,
            'start_time' => $start,
            'end_time'   => $end,
            'status'     => AppointmentStatus::CONFIRMED->value,
        ], $overrides));
    }

    private function assertRejectedWith(string $needle, callable $callback): void
    {
        try {
            $callback();
            $this->fail("Expected ValidationException containing [{$needle}], none thrown.");
        } catch (ValidationException $e) {
            $this->assertStringContainsString(
                $needle,
                json_encode($e->errors()),
                "Rejection did not mention [{$needle}]."
            );
        }
    }

    // ─── Hard rules bind everyone ─────────────────────

    public function test_non_working_day_rejected_even_for_staff(): void
    {
        // 2026-08-23 is a Sunday (0), not in default working_days
        $dto = $this->dto('2026-08-23 10:00:00', '2026-08-23 10:30:00');

        $this->assertRejectedWith('working_days', fn() => $this->rules()->assertCanBook($dto, isStaff: false));
        $this->assertRejectedWith('working_days', fn() => $this->rules()->assertCanBook($dto, isStaff: true));
    }

    public function test_outside_clinic_hours_rejected(): void
    {
        $dto = $this->dto('2026-08-19 18:30:00', '2026-08-19 19:00:00');

        $this->assertRejectedWith('clinic_opens_at', fn() => $this->rules()->assertCanBook($dto, isStaff: true));
    }

    public function test_lunch_overlap_rejected(): void
    {
        $dto = $this->dto('2026-08-19 11:45:00', '2026-08-19 12:15:00');

        $this->assertRejectedWith('lunch_break_start', fn() => $this->rules()->assertCanBook($dto, isStaff: true));
    }

    // ─── Soft rules bind patients, not staff ──────────

    public function test_lead_time_rejects_patient_but_not_staff(): void
    {
        // now = 08:00, lead time 2h, so 09:00 is too soon
        $dto = $this->dto('2026-08-19 09:00:00', '2026-08-19 09:30:00');

        $this->assertRejectedWith('booking_lead_time_hours', fn() => $this->rules()->assertCanBook($dto, isStaff: false));

        $this->rules()->assertCanBook($dto, isStaff: true);
        $this->assertTrue(true);
    }

    public function test_same_day_toggle_rejects_patient(): void
    {
        $dto = $this->dto('2026-08-19 15:00:00', '2026-08-19 15:30:00');

        $this->assertRejectedWith(
            'allow_same_day_booking',
            fn() => $this->rules(['allowSameDayBooking' => false])->assertCanBook($dto, isStaff: false)
        );
    }

    public function test_max_advance_rejects_patient(): void
    {
        // Friday far beyond a 7-day window
        $dto = $this->dto('2026-09-18 10:00:00', '2026-09-18 10:30:00');

        $this->assertRejectedWith(
            'max_advance_booking_days',
            fn() => $this->rules(['maxAdvanceDays' => 7])->assertCanBook($dto, isStaff: false)
        );
    }

    public function test_dentist_daily_cap_rejects_patient_but_not_staff(): void
    {
        $this->makeAppointment('2026-08-19 14:00:00', '2026-08-19 14:30:00');

        $dto = $this->dto('2026-08-19 15:00:00', '2026-08-19 15:30:00');
        $rules = fn() => $this->rules(['maxPerDentistPerDay' => 1]);

        $this->assertRejectedWith('max_appointments_per_dentist_per_day', fn() => $rules()->assertCanBook($dto, isStaff: false));

        $rules()->assertCanBook($dto, isStaff: true);
        $this->assertTrue(true);
    }

    public function test_concurrent_cap_counts_other_doctors(): void
    {
        $otherDoctorUser = User::factory()->create();
        $otherDoctor = Doctor::create([
            'user_id'        => $otherDoctorUser->id,
            'license_number' => 'LIC-002',
        ]);

        $this->makeAppointment('2026-08-19 15:00:00', '2026-08-19 15:30:00', ['doctor_id' => $otherDoctor->id]);

        $dto = $this->dto('2026-08-19 15:00:00', '2026-08-19 15:30:00');

        $this->assertRejectedWith(
            'max_concurrent_appointments',
            fn() => $this->rules(['maxConcurrent' => 1])->assertCanBook($dto, isStaff: false)
        );
    }

    public function test_future_cap_rejects_patient(): void
    {
        $this->makeAppointment('2026-08-20 10:00:00', '2026-08-20 10:30:00');

        $dto = $this->dto('2026-08-21 10:00:00', '2026-08-21 10:30:00');

        $this->assertRejectedWith(
            'max_future_appointments_per_patient',
            fn() => $this->rules(['maxFuturePerPatient' => 1])->assertCanBook($dto, isStaff: false)
        );
    }

    public function test_online_booking_switch_rejects_patient(): void
    {
        $dto = $this->dto('2026-08-19 15:00:00', '2026-08-19 15:30:00');

        $this->assertRejectedWith(
            'allow_online_booking',
            fn() => $this->rules(['allowOnlineBooking' => false])->assertCanBook($dto, isStaff: false)
        );
    }

    // ─── Cancellation cutoff ──────────────────────────

    public function test_cancellation_inside_cutoff_rejects_patient_but_not_staff(): void
    {
        // Starts in 10 hours; cutoff is 24 — the deadline has passed.
        $appointment = $this->makeAppointment('2026-08-19 18:00:00', '2026-08-19 18:30:00');

        $this->assertRejectedWith(
            'cancellation_window_hours',
            fn() => $this->rules()->assertCanCancel($appointment, isStaff: false)
        );

        $this->rules()->assertCanCancel($appointment, isStaff: true);
        $this->assertTrue(true);
    }

    public function test_cancellation_outside_cutoff_allowed_for_patient(): void
    {
        $appointment = $this->makeAppointment('2026-08-21 10:00:00', '2026-08-21 10:30:00');

        $this->rules()->assertCanCancel($appointment, isStaff: false);
        $this->assertTrue(true);
    }

    // ─── Slot generation ──────────────────────────────

    private function slotsWith(array $overrides): array
    {
        $this->app->instance(AppointmentSettings::class, $this->settings($overrides));

        return $this->app->make(AppointmentRepository::class)
            ->getAvailableSlots($this->doctor->id, $this->branch->id, '2026-08-19');
    }

    public function test_slots_empty_on_non_working_day(): void
    {
        $this->app->instance(AppointmentSettings::class, $this->settings());

        $result = $this->app->make(AppointmentRepository::class)
            ->getAvailableSlots($this->doctor->id, $this->branch->id, '2026-08-23'); // Sunday

        $this->assertSame([], $result['slots']);
    }

    public function test_slots_stride_includes_buffer(): void
    {
        $result = $this->slotsWith(['bufferMinutes' => 15]);

        $this->assertNotEmpty($result['slots']);

        $first  = Carbon::parse($result['slots'][0]['start_time']);
        $second = Carbon::parse($result['slots'][1]['start_time']);

        $this->assertSame(45, (int) $first->diffInMinutes($second));
        // Slot length stays the duration; only the gap grows.
        $this->assertSame(
            30,
            (int) Carbon::parse($result['slots'][0]['start_time'])
                ->diffInMinutes(Carbon::parse($result['slots'][0]['end_time']))
        );
    }

    public function test_slots_overlapping_lunch_are_blocked_with_reason(): void
    {
        $result = $this->slotsWith([]);

        $lunchSlots = array_filter(
            $result['slots'],
            fn($s) => $s['start_time'] === '2026-08-19 12:00:00' || $s['start_time'] === '2026-08-19 12:30:00'
        );

        $this->assertNotEmpty($lunchSlots);
        foreach ($lunchSlots as $slot) {
            $this->assertFalse($slot['is_available']);
            $this->assertSame('lunch_break_start', $slot['unavailable_reason']);
        }
    }

    public function test_booked_slot_is_blocked_with_reason(): void
    {
        $this->makeAppointment('2026-08-19 14:00:00', '2026-08-19 14:30:00');

        $result = $this->slotsWith([]);

        $booked = collect($result['slots'])->firstWhere('start_time', '2026-08-19 14:00:00');

        $this->assertNotNull($booked);
        $this->assertFalse($booked['is_available']);
        $this->assertSame('booked', $booked['unavailable_reason']);
    }

    // ─── HTTP endpoint ────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'appointment_slot_duration'  => 30,
            'appointment_buffer_minutes' => 0,
            'clinic_opens_at'            => '09:00',
            'clinic_closes_at'           => '18:00',
            'lunch_break_start'          => '12:00',
            'lunch_break_end'            => '13:00',
            'working_days'               => [1, 2, 3, 4, 5, 6],

            'max_appointments_per_dentist_per_day' => 12,
            'max_appointments_per_day'             => 60,
            'max_concurrent_appointments'          => 3,

            'booking_lead_time_hours'             => 2,
            'max_advance_booking_days'            => 90,
            'allow_same_day_booking'              => true,
            'max_future_appointments_per_patient' => 3,
            'allow_online_booking'                => true,

            'cancellation_window_hours' => 24,
            'late_cancellation_fee'     => 500.0,
            'no_show_fee'               => 300.0,
            'no_shows_before_block'     => 3,

            'reminder_offsets'                => [24, 1],
            'send_booking_confirmation_email' => true,
            'send_cancellation_email'         => true,
            'send_followup_email'             => false,
            'followup_email_hours_after'      => 24,

            'enable_waitlist'               => false,
            'waitlist_offer_window_minutes' => 120,
        ], $overrides);
    }

    private function actingAsSettingsAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('setting.view', 'setting.update');
        Passport::actingAs($admin);

        return $admin;
    }

    public function test_get_requires_permission(): void
    {
        Passport::actingAs($this->patient);

        $this->getJson('/api/v1/appointment-settings')->assertForbidden();
    }

    public function test_get_returns_defaults_when_unseeded(): void
    {
        $this->actingAsSettingsAdmin();

        $this->getJson('/api/v1/appointment-settings')
            ->assertOk()
            ->assertJsonPath('data.appointment_slot_duration', 30)
            ->assertJsonPath('data.clinic_opens_at', '09:00')
            ->assertJsonPath('data.working_days', [1, 2, 3, 4, 5, 6]);
    }

    public function test_put_round_trips_values(): void
    {
        $this->actingAsSettingsAdmin();

        $this->putJson('/api/v1/appointment-settings', $this->validPayload([
            'appointment_slot_duration'  => 45,
            'appointment_buffer_minutes' => 10,
            'working_days'               => [1, 3, 5],
            'allow_same_day_booking'     => false,
        ]))
            ->assertOk()
            ->assertJsonPath('data.appointment_slot_duration', 45)
            ->assertJsonPath('data.appointment_buffer_minutes', 10)
            ->assertJsonPath('data.working_days', [1, 3, 5])
            ->assertJsonPath('data.allow_same_day_booking', false);

        // Fresh GET proves persistence + cache bust, not just the echo.
        $this->getJson('/api/v1/appointment-settings')
            ->assertOk()
            ->assertJsonPath('data.appointment_slot_duration', 45)
            ->assertJsonPath('data.working_days', [1, 3, 5]);
    }

    public function test_put_rejects_lunch_outside_clinic_hours(): void
    {
        $this->actingAsSettingsAdmin();

        $this->putJson('/api/v1/appointment-settings', $this->validPayload([
            'lunch_break_start' => '08:00',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['lunch_break_start']);
    }

    public function test_put_rejects_second_reminder_not_closer_than_first(): void
    {
        $this->actingAsSettingsAdmin();

        $this->putJson('/api/v1/appointment-settings', $this->validPayload([
            'reminder_offsets' => [1, 24],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['reminder_offsets']);
    }

    public function test_put_requires_permission(): void
    {
        Passport::actingAs($this->patient);

        $this->putJson('/api/v1/appointment-settings', $this->validPayload())
            ->assertForbidden();
    }
}
