<?php

namespace Tests\Feature;

use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\Treatment;
use App\Models\TreatmentConsumable;
use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\User;
use App\Notifications\TreatmentPlanStockShortfallNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Recording the supplies a completed treatment plan used.
 *
 * Plans, unlike appointments, have no branch and no automatic deduction —
 * staff submit actual quantities against a branch they belong to, exactly
 * once per plan. The ledger itself is the idempotency record.
 */
class TreatmentPlanConsumablesTest extends TestCase
{
    use RefreshDatabase;

    private const NOW = '2026-08-19 08:00:00';
    private const BASE = '/api/v1';

    private Branch $main;
    private Branch $other;
    private User $recorder;
    private User $watcher;
    private User $outsider;
    private Doctor $doctor;
    private User $patient;
    private Item $anesthetic;
    private Item $cotton;
    private Treatment $extraction;
    private Treatment $cleaning;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));
        Notification::fake();

        foreach ([
            'treatment-plan.record-consumables',
            'treatment-plan.mark-completed',
            'treatment-plan.viewAny',
            'inventory.viewAny',
        ] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'api']);
        }

        $recorderRole = Role::create(['name' => 'recorder', 'guard_name' => 'api']);
        $recorderRole->givePermissionTo([
            'treatment-plan.record-consumables',
            'treatment-plan.mark-completed',
            'treatment-plan.viewAny',
        ]);

        $watcherRole = Role::create(['name' => 'watcher', 'guard_name' => 'api']);
        $watcherRole->givePermissionTo('inventory.viewAny');

        $this->main = Branch::create([
            'name' => 'Main', 'branch_code' => 'MAIN', 'address' => '1 Test St',
        ]);
        $this->other = Branch::create([
            'name' => 'Other', 'branch_code' => 'OTH', 'address' => '2 Test St',
        ]);

        $this->recorder = User::factory()->create();
        $this->recorder->assignRole($recorderRole);
        $this->recorder->branches()->attach($this->main->id);

        $this->watcher = User::factory()->create();
        $this->watcher->assignRole($watcherRole);
        $this->watcher->branches()->attach($this->main->id);

        // Permission but no branch membership anywhere.
        $this->outsider = User::factory()->create();
        $this->outsider->assignRole($recorderRole);

        $doctorUser = User::factory()->create();
        $this->doctor = Doctor::create([
            'user_id' => $doctorUser->id, 'license_number' => 'LIC-001',
        ]);

        $this->patient = User::factory()->create();

        $this->anesthetic = Item::create([
            'name' => 'Lidocaine 2% carpule', 'sku' => 'ANES-LIDO-2',
            'category' => 'Anesthetics', 'unit_of_measure' => 'carpule',
            'minimum_threshold' => 20,
        ]);
        $this->cotton = Item::create([
            'name' => 'Cotton roll', 'sku' => 'CONS-COTTON',
            'category' => 'Consumables', 'unit_of_measure' => 'piece',
            'minimum_threshold' => 50,
        ]);

        $this->extraction = Treatment::create([
            'name' => 'Tooth Extraction', 'price' => 2000.00,
        ]);
        $this->cleaning = Treatment::create([
            'name' => 'Oral Prophylaxis', 'price' => 1200.00,
        ]);

        TreatmentConsumable::create([
            'treatment_id' => $this->extraction->id,
            'item_id' => $this->anesthetic->id,
            'quantity_per_use' => 2,
        ]);
        TreatmentConsumable::create([
            'treatment_id' => $this->extraction->id,
            'item_id' => $this->cotton->id,
            'quantity_per_use' => 4,
            'is_optional' => true,
        ]);
        TreatmentConsumable::create([
            'treatment_id' => $this->cleaning->id,
            'item_id' => $this->anesthetic->id,
            'quantity_per_use' => 1,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ── Helpers ───────────────────────────────────────

    /** @param list<array{0:Treatment,1:int}> $steps [treatment, quantity] */
    private function plan(string $status = 'completed', array $steps = []): TreatmentPlan
    {
        $plan = TreatmentPlan::create([
            'user_id' => $this->patient->id,
            'doctor_id' => $this->doctor->id,
            'name' => 'Molar rescue mission',
            'status' => $status,
        ]);

        foreach ($steps as $index => [$treatment, $quantity]) {
            TreatmentPlanItem::create([
                'treatment_plan_id' => $plan->id,
                'treatment_id' => $treatment->id,
                'sequence_order' => $index + 1,
                'quantity' => $quantity,
                'unit_price' => $treatment->price,
                'estimated_cost' => $treatment->price * $quantity,
            ]);
        }

        return $plan;
    }

    private function stockIn(Item $item, int $quantity): void
    {
        app(StockLedger::class)->record(new RecordMovementDTO(
            branchId: (int) $this->main->id,
            itemId: (int) $item->id,
            type: StockMovementType::STOCK_IN,
            quantityDelta: $quantity,
            lotNumber: 'LOT-' . $item->id,
            expiryDate: '2027-06-01',
        ));
    }

    private function record(TreatmentPlan $plan, array $lines, ?int $branchId = null)
    {
        return $this->postJson(self::BASE . "/treatment-plans/{$plan->id}/consumables", [
            'branch_id' => $branchId ?? $this->main->id,
            'lines' => $lines,
        ]);
    }

    // ── Suggestions ───────────────────────────────────

    public function test_suggestions_multiply_recipes_by_step_quantity_and_fold_items(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');

        // 3 extractions (2 carpules each) + 1 cleaning (1 carpule) = 7 carpules;
        // cotton appears only on the optional extraction line: 4 x 3 = 12.
        $plan = $this->plan('completed', [[$this->extraction, 3], [$this->cleaning, 1]]);

        $response = $this->getJson(self::BASE . "/treatment-plans/{$plan->id}/consumables");

        $response->assertOk()
            ->assertJsonPath('data.recorded', false)
            ->assertJsonPath('data.movements', [])
            ->assertJsonCount(2, 'data.suggested_lines')
            ->assertJsonFragment([
                'item_id' => $this->anesthetic->id,
                'suggested_quantity' => 7,
                'is_optional' => false,
            ])
            ->assertJsonFragment([
                'item_id' => $this->cotton->id,
                'suggested_quantity' => 12,
                'is_optional' => true,
            ]);
    }

    // ── Recording ─────────────────────────────────────

    public function test_recording_writes_ledger_movements_referencing_the_plan(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');
        $this->stockIn($this->anesthetic, 50);

        $plan = $this->plan('completed', [[$this->extraction, 2]]);

        $response = $this->record($plan, [
            ['item_id' => $this->anesthetic->id, 'quantity' => 4],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.recorded', true)
            ->assertJsonPath('data.shortfalls', [])
            ->assertJsonPath('data.movements.0.type', 'consumption')
            ->assertJsonPath('data.movements.0.reference_type', 'TreatmentPlan')
            ->assertJsonPath('data.movements.0.reference_id', $plan->id);

        $movement = StockMovement::query()
            ->where('type', StockMovementType::CONSUMPTION)
            ->sole();
        $this->assertSame(TreatmentPlan::class, $movement->reference_type);
        $this->assertSame(-4, $movement->quantity_delta);
        $this->assertSame($this->recorder->id, $movement->performed_by);

        $inventory = Inventory::where('branch_id', $this->main->id)
            ->where('item_id', $this->anesthetic->id)
            ->sole();
        $this->assertSame(46, (int) $inventory->quantity);
    }

    public function test_a_second_recording_is_rejected_and_changes_nothing(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');
        $this->stockIn($this->anesthetic, 50);

        $plan = $this->plan('completed', [[$this->extraction, 1]]);

        $this->record($plan, [['item_id' => $this->anesthetic->id, 'quantity' => 2]])
            ->assertCreated();

        $before = StockMovement::count();

        $this->record($plan, [['item_id' => $this->anesthetic->id, 'quantity' => 2]])
            ->assertStatus(409);

        $this->assertSame($before, StockMovement::count());
    }

    public function test_recording_requires_the_permission(): void
    {
        $unblessed = User::factory()->create();
        $unblessed->branches()->attach($this->main->id);
        Passport::actingAs($unblessed, ['*'], 'api');

        $plan = $this->plan('completed', [[$this->extraction, 1]]);

        $this->record($plan, [['item_id' => $this->anesthetic->id, 'quantity' => 1]])
            ->assertForbidden();
    }

    public function test_recording_requires_membership_in_the_branch(): void
    {
        Passport::actingAs($this->outsider, ['*'], 'api');

        $plan = $this->plan('completed', [[$this->extraction, 1]]);

        $this->record($plan, [['item_id' => $this->anesthetic->id, 'quantity' => 1]])
            ->assertForbidden();
    }

    public function test_recording_rejects_a_plan_that_is_not_completed(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');

        $plan = $this->plan('accepted', [[$this->extraction, 1]]);

        $this->record($plan, [['item_id' => $this->anesthetic->id, 'quantity' => 1]])
            ->assertStatus(422);

        $this->assertSame(0, StockMovement::count());
    }

    public function test_a_shortfall_records_flags_and_notifies_rather_than_failing(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');
        $this->stockIn($this->anesthetic, 3);

        $plan = $this->plan('completed', [[$this->extraction, 1]]);

        $response = $this->record($plan, [
            ['item_id' => $this->anesthetic->id, 'quantity' => 5],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.shortfalls.0.item_id', $this->anesthetic->id)
            ->assertJsonPath('data.shortfalls.0.short_by', 2);

        $inventory = Inventory::where('branch_id', $this->main->id)
            ->where('item_id', $this->anesthetic->id)
            ->sole();
        $this->assertSame(-2, (int) $inventory->quantity);

        Notification::assertSentTo(
            $this->watcher,
            TreatmentPlanStockShortfallNotification::class,
            fn ($notification): bool => $notification->plan->id === $plan->id
                && $notification->branchId === $this->main->id,
        );
    }

    public function test_the_dialog_sees_what_was_recorded_afterwards(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');
        $this->stockIn($this->anesthetic, 50);

        $plan = $this->plan('completed', [[$this->extraction, 1]]);

        $this->record($plan, [['item_id' => $this->anesthetic->id, 'quantity' => 2]])
            ->assertCreated();

        $this->getJson(self::BASE . "/treatment-plans/{$plan->id}/consumables")
            ->assertOk()
            ->assertJsonPath('data.recorded', true)
            ->assertJsonCount(1, 'data.movements')
            ->assertJsonPath('data.movements.0.quantity_delta', -2);
    }

    public function test_the_plan_list_carries_the_recorded_flag(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');
        $this->stockIn($this->anesthetic, 50);

        $recorded = $this->plan('completed', [[$this->extraction, 1]]);
        $untouched = $this->plan('completed', [[$this->cleaning, 1]]);

        $this->record($recorded, [['item_id' => $this->anesthetic->id, 'quantity' => 2]])
            ->assertCreated();

        $response = $this->getJson(self::BASE . '/treatment-plans?limit=10')->assertOk();

        $flags = collect($response->json('data.records'))
            ->mapWithKeys(fn (array $row): array => [$row['id'] => $row['consumables_recorded']]);

        $this->assertTrue($flags[$recorded->id]);
        $this->assertFalse($flags[$untouched->id]);
    }

    // ── Regression: status route param fix ────────────

    public function test_marking_a_plan_completed_over_http_works_again(): void
    {
        Passport::actingAs($this->recorder, ['*'], 'api');

        $plan = $this->plan('accepted', [[$this->extraction, 1]]);

        // Before the {treatment_plan} param fix the request read a param name
        // that never existed, so authorize() saw null and 403'd everyone.
        $this->patchJson(self::BASE . "/treatment-plans/{$plan->id}/status", [
            'status' => 'completed',
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }
}
