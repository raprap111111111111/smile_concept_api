<?php

namespace Tests\Feature;

use App\Domain\Appointments\Actions\UpdateAppointmentStatusAction;
use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Domain\Settings\Services\SettingService;
use App\Enums\AppointmentStatus;
use App\Enums\StockMovementType;
use App\Models\Appointment;
use App\Models\AppointmentTreatment;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Treatment;
use App\Models\TreatmentConsumable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Automatic deduction: the thing that makes stock track patient volume instead
 * of somebody's memory.
 *
 * The two rules that matter most are pinned here — it never blocks clinical
 * work, and it never deducts twice.
 */
class AppointmentStockDeductionTest extends TestCase
{
    use RefreshDatabase;

    private const NOW = '2026-08-19 08:00:00';

    private Branch $branch;
    private Doctor $doctor;
    private User $patient;
    private Item $anesthetic;
    private Item $cottonRoll;
    private Treatment $extraction;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));
        Notification::fake();

        Role::create(['name' => 'patient', 'guard_name' => 'api']);
        Permission::create(['name' => 'inventory.viewAny', 'guard_name' => 'api']);

        $this->branch = Branch::create([
            'name' => 'Main', 'branch_code' => 'MAIN', 'address' => '1 Test St',
        ]);

        $doctorUser = User::factory()->create();
        $this->doctor = Doctor::create([
            'user_id' => $doctorUser->id, 'license_number' => 'LIC-001',
        ]);

        $this->patient = User::factory()->create();
        $this->patient->assignRole('patient');

        $this->anesthetic = Item::create([
            'name' => 'Lidocaine 2% carpule', 'sku' => 'ANES-LIDO-2',
            'category' => 'Anesthetics', 'unit_of_measure' => 'carpule',
            'minimum_threshold' => 20,
        ]);

        $this->cottonRoll = Item::create([
            'name' => 'Cotton roll', 'sku' => 'CON-COT-1',
            'category' => 'Hygiene', 'unit_of_measure' => 'piece',
            'minimum_threshold' => 50,
        ]);

        $this->extraction = Treatment::create([
            'name' => 'Extraction', 'price' => 1500,
            'estimated_duration_minutes' => 45, 'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ── The main path ─────────────────────────────────

    public function test_completing_an_appointment_deducts_its_recipe(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->recipe($this->extraction, $this->cottonRoll, 5);

        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');
        $this->stockIn($this->cottonRoll, 100, null, 'C1');

        $appointment = $this->appointmentWith([[$this->extraction, 1]]);

        $this->complete($appointment);

        $this->assertSame(48, $this->quantityOf($this->anesthetic));
        $this->assertSame(95, $this->quantityOf($this->cottonRoll));
    }

    public function test_procedure_quantity_multiplies_the_recipe(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');

        // Three extractions in one visit: 2 carpules each.
        $appointment = $this->appointmentWith([[$this->extraction, 3]]);

        $this->complete($appointment);

        $this->assertSame(44, $this->quantityOf($this->anesthetic));

        $movement = StockMovement::where('type', StockMovementType::CONSUMPTION)->sole();
        $this->assertSame(-6, $movement->quantity_delta);
    }

    public function test_the_same_item_across_procedures_is_one_withdrawal(): void
    {
        $filling = Treatment::create([
            'name' => 'Filling', 'price' => 900,
            'estimated_duration_minutes' => 30, 'is_active' => true,
        ]);

        $this->recipe($this->extraction, $this->cottonRoll, 5);
        $this->recipe($filling, $this->cottonRoll, 3);

        $this->stockIn($this->cottonRoll, 100, null, 'C1');

        $appointment = $this->appointmentWith([
            [$this->extraction, 2],
            [$filling, 1],
        ]);

        $this->complete($appointment);

        // 5×2 + 3×1 = 13, as a single line rather than one per procedure.
        $consumption = StockMovement::where('type', StockMovementType::CONSUMPTION)->get();
        $this->assertCount(1, $consumption);
        $this->assertSame(-13, $consumption->first()->quantity_delta);
        $this->assertSame(87, $this->quantityOf($this->cottonRoll));
    }

    public function test_deduction_draws_the_earliest_expiring_lot_first(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);

        $this->stockIn($this->anesthetic, 10, '2027-06-01', 'LATE');
        $this->stockIn($this->anesthetic, 10, '2026-10-01', 'SOON');

        $this->complete($this->appointmentWith([[$this->extraction, 3]]));

        $this->assertSame(4, $this->batch('SOON')->quantity_remaining);
        $this->assertSame(10, $this->batch('LATE')->quantity_remaining);
    }

    public function test_optional_lines_are_not_deducted(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->recipe($this->extraction, $this->cottonRoll, 5, optional: true);

        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');
        $this->stockIn($this->cottonRoll, 100, null, 'C1');

        $this->complete($this->appointmentWith([[$this->extraction, 1]]));

        $this->assertSame(48, $this->quantityOf($this->anesthetic));
        $this->assertSame(100, $this->quantityOf($this->cottonRoll), 'optional stays untouched');
    }

    public function test_the_movement_points_back_at_the_appointment(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');

        $appointment = $this->appointmentWith([[$this->extraction, 1]]);
        $this->complete($appointment);

        $movement = StockMovement::where('type', StockMovementType::CONSUMPTION)->sole();

        $this->assertSame(Appointment::class, $movement->reference_type);
        $this->assertSame($appointment->id, $movement->reference_id);
        $this->assertSame($this->branch->id, $movement->branch_id);
    }

    // ── Idempotency ───────────────────────────────────

    public function test_completing_twice_deducts_once(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');

        $appointment = $this->appointmentWith([[$this->extraction, 1]]);

        $this->complete($appointment);
        // validateTransition() treats same-status as a no-op and returns, so
        // execute() runs end to end again. The ledger lookup is what stops the
        // second draw.
        $this->complete($appointment->fresh());

        $this->assertSame(48, $this->quantityOf($this->anesthetic));
        $this->assertSame(1, StockMovement::where('type', StockMovementType::CONSUMPTION)->count());
    }

    // ── Never blocking ────────────────────────────────

    public function test_a_shortfall_does_not_stop_the_appointment_completing(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->stockIn($this->anesthetic, 3, '2027-02-01', 'A1');

        $appointment = $this->appointmentWith([[$this->extraction, 3]]);

        // Needs 6, has 3. Running out of anesthetic on record is not a reason
        // to refuse to check the patient out.
        $completed = $this->complete($appointment);

        $this->assertSame(AppointmentStatus::COMPLETED->value, $completed->status->value ?? $completed->status);
        $this->assertSame(-3, $this->quantityOf($this->anesthetic));
        $this->assertSame(0, $this->batch('A1')->quantity_remaining);

        $shortfall = StockMovement::whereNull('inventory_batch_id')
            ->where('type', StockMovementType::CONSUMPTION)
            ->sole();
        $this->assertSame(-3, $shortfall->quantity_delta);
    }

    public function test_completing_with_no_stock_at_all_still_completes(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);

        $completed = $this->complete($this->appointmentWith([[$this->extraction, 1]]));

        $this->assertSame(AppointmentStatus::COMPLETED->value, $completed->status->value ?? $completed->status);
        $this->assertSame(-2, $this->quantityOf($this->anesthetic));
    }

    public function test_a_shortfall_notifies_stock_watchers_at_that_branch(): void
    {
        $keeper = User::factory()->create();
        $keeper->givePermissionTo('inventory.viewAny');
        $keeper->branches()->attach($this->branch->id);

        $elsewhere = User::factory()->create();
        $elsewhere->givePermissionTo('inventory.viewAny');

        $this->recipe($this->extraction, $this->anesthetic, 2);

        $this->complete($this->appointmentWith([[$this->extraction, 1]]));

        Notification::assertSentTo($keeper, \App\Notifications\StockShortfallNotification::class);
        // Someone at another branch cannot restock this cupboard.
        Notification::assertNotSentTo($elsewhere, \App\Notifications\StockShortfallNotification::class);
    }

    public function test_a_shortfall_is_written_to_the_audit_trail(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);

        $appointment = $this->appointmentWith([[$this->extraction, 1]]);
        $this->complete($appointment);

        $this->assertDatabaseHas('activity_logs', [
            'action'       => 'stock_shortfall',
            'subject_type' => Appointment::class,
            'subject_id'   => $appointment->id,
        ]);
    }

    // ── Skips ─────────────────────────────────────────

    public function test_nothing_is_deducted_when_the_setting_is_off(): void
    {
        app(SettingService::class)->set('inventory_auto_deduct_enabled', false);

        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');

        $this->complete($this->appointmentWith([[$this->extraction, 1]]));

        $this->assertSame(50, $this->quantityOf($this->anesthetic));
        $this->assertSame(0, StockMovement::where('type', StockMovementType::CONSUMPTION)->count());
    }

    public function test_a_treatment_without_a_recipe_deducts_nothing(): void
    {
        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');

        $this->complete($this->appointmentWith([[$this->extraction, 1]]));

        $this->assertSame(50, $this->quantityOf($this->anesthetic));
    }

    public function test_cancelling_deducts_nothing(): void
    {
        $this->recipe($this->extraction, $this->anesthetic, 2);
        $this->stockIn($this->anesthetic, 50, '2027-02-01', 'A1');

        $appointment = $this->appointmentWith([[$this->extraction, 1]]);

        // Acting as staff: the appointment starts in two hours and the
        // cancellation cutoff (cancellation_window_hours) binds patients only.
        Role::findOrCreate('receptionist', 'api');
        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');
        $this->actingAs($receptionist);

        app(UpdateAppointmentStatusAction::class)
            ->execute($appointment, AppointmentStatus::CANCELLED, 'Patient rescheduled.');

        $this->assertSame(50, $this->quantityOf($this->anesthetic));
    }

    // ── Helpers ───────────────────────────────────────

    private function recipe(Treatment $treatment, Item $item, int $perUse, bool $optional = false): void
    {
        TreatmentConsumable::create([
            'treatment_id'     => $treatment->id,
            'item_id'          => $item->id,
            'quantity_per_use' => $perUse,
            'is_optional'      => $optional,
        ]);
    }

    private function stockIn(Item $item, int $quantity, ?string $expiry, string $lot): void
    {
        app(StockLedger::class)->record(new RecordMovementDTO(
            branchId: $this->branch->id,
            itemId: $item->id,
            type: StockMovementType::STOCK_IN,
            quantityDelta: $quantity,
            lotNumber: $lot,
            expiryDate: $expiry,
        ));
    }

    /** @param list<array{0: Treatment, 1: int}> $procedures */
    private function appointmentWith(array $procedures): Appointment
    {
        $appointment = Appointment::create([
            'user_id'    => $this->patient->id,
            'doctor_id'  => $this->doctor->id,
            'branch_id'  => $this->branch->id,
            'start_time' => Carbon::parse(self::NOW)->addHours(2),
            'end_time'   => Carbon::parse(self::NOW)->addHours(3),
            'status'     => AppointmentStatus::CONFIRMED->value,
            'created_by' => $this->patient->id,
        ]);

        foreach ($procedures as [$treatment, $quantity]) {
            AppointmentTreatment::create([
                'appointment_id' => $appointment->id,
                'treatment_id'   => $treatment->id,
                'quantity'       => $quantity,
                'price_charged'  => $treatment->price,
            ]);
        }

        return $appointment;
    }

    private function complete(Appointment $appointment): Appointment
    {
        return app(UpdateAppointmentStatusAction::class)
            ->execute($appointment, AppointmentStatus::COMPLETED);
    }

    private function quantityOf(Item $item): int
    {
        return (int) Inventory::where('branch_id', $this->branch->id)
            ->where('item_id', $item->id)
            ->value('quantity');
    }

    private function batch(string $lot): InventoryBatch
    {
        return InventoryBatch::where('lot_number', $lot)->sole();
    }
}
