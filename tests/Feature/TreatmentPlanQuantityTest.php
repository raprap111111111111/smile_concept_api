<?php

namespace Tests\Feature;

use App\Domain\TreatmentPlans\Actions\CreateTreatmentPlanAction;
use App\Domain\TreatmentPlans\Actions\UpdateTreatmentPlanAction;
use App\Domain\TreatmentPlans\DTOs\CreateTreatmentPlanDTO;
use App\Domain\TreatmentPlans\DTOs\CreateTreatmentPlanItemDTO;
use App\Domain\TreatmentPlans\DTOs\UpdateTreatmentPlanDTO;
use App\Enums\TreatmentPlanStatus;
use App\Http\Resources\v1\TreatmentPlanResource;
use App\Models\Doctor;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * The plan builder has always offered a per-step quantity, but nothing stored
 * or multiplied it: three fillings quoted as one. These lock the arithmetic
 * and the serialised shape the Flutter client reads.
 */
class TreatmentPlanQuantityTest extends TestCase
{
    use RefreshDatabase;

    private User $patient;

    private Doctor $doctor;

    private Treatment $filling;

    private Treatment $cleaning;

    protected function setUp(): void
    {
        parent::setUp();

        $this->patient = User::factory()->create();

        $doctorUser = User::factory()->create(['name' => 'Reyes']);
        $this->doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'license_number' => 'LIC-0001',
            'specialization' => 'Orthodontics',
        ]);

        $this->filling = Treatment::create([
            'name' => 'Composite Filling',
            'price' => 1500.00,
            'estimated_duration_minutes' => 45,
        ]);

        $this->cleaning = Treatment::create([
            'name' => 'Deep Cleaning',
            'price' => 800.00,
            'estimated_duration_minutes' => 30,
        ]);
    }

    private function createPlan(array $items)
    {
        return app(CreateTreatmentPlanAction::class)->execute(
            new CreateTreatmentPlanDTO(
                userId: $this->patient->id,
                doctorId: $this->doctor->id,
                name: 'Restoration Plan',
                notes: null,
                items: $items,
            )
        );
    }

    public function test_line_total_multiplies_the_catalog_price_by_quantity(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
                quantity: 3,
            ),
        ]);

        $item = $plan->items->first();

        $this->assertSame(3, $item->quantity);
        $this->assertEquals(1500.00, (float) $item->unit_price);
        $this->assertEquals(4500.00, (float) $item->estimated_cost);
        $this->assertEquals(4500.00, (float) $plan->total_estimated_amount);
    }

    public function test_plan_total_sums_every_line_total(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
                quantity: 3,
            ),
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->cleaning->id,
                sequenceOrder: 2,
                quantity: 2,
            ),
        ]);

        // 3 x 1500 + 2 x 800
        $this->assertEquals(6100.00, (float) $plan->total_estimated_amount);
    }

    public function test_quantity_defaults_to_one(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
            ),
        ]);

        $item = $plan->items->first();

        $this->assertSame(1, $item->quantity);
        $this->assertEquals(1500.00, (float) $item->estimated_cost);
        $this->assertEquals(1500.00, (float) $plan->total_estimated_amount);
    }

    public function test_a_later_catalog_price_change_does_not_move_the_quote(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
                quantity: 2,
            ),
        ]);

        $this->filling->update(['price' => 9999.00]);

        $plan->refresh()->load('items');

        $this->assertEquals(1500.00, (float) $plan->items->first()->unit_price);
        $this->assertEquals(3000.00, (float) $plan->total_estimated_amount);
    }

    public function test_the_resource_exposes_quantity_and_unit_price(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
                quantity: 3,
            ),
        ]);

        $payload = (new TreatmentPlanResource($plan))->toArray(Request::create('/'));
        $step = $payload['steps']->first();

        $this->assertSame(3, $step['quantity']);
        $this->assertEquals(1500.00, (float) $step['unit_price']);
        $this->assertEquals(4500.00, (float) $step['estimated_cost']);
        $this->assertSame('Composite Filling', $step['treatment_name']);
    }

    public function test_a_status_only_update_no_longer_fatals(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
                quantity: 2,
            ),
        ]);

        $updated = app(UpdateTreatmentPlanAction::class)->execute(
            $plan,
            new UpdateTreatmentPlanDTO(status: TreatmentPlanStatus::ACCEPTED),
        );

        $this->assertSame(TreatmentPlanStatus::ACCEPTED, $updated->status);
        // Items untouched when the payload carries none.
        $this->assertEquals(3000.00, (float) $updated->total_estimated_amount);
    }

    public function test_updating_the_items_requotes_with_quantity(): void
    {
        $plan = $this->createPlan([
            new CreateTreatmentPlanItemDTO(
                treatmentId: $this->filling->id,
                sequenceOrder: 1,
                quantity: 1,
            ),
        ]);

        $updated = app(UpdateTreatmentPlanAction::class)->execute(
            $plan,
            new UpdateTreatmentPlanDTO(
                userId: null,
                doctorId: null,
                name: null,
                status: null,
                notes: null,
                items: [
                    new CreateTreatmentPlanItemDTO(
                        treatmentId: $this->cleaning->id,
                        sequenceOrder: 1,
                        quantity: 4,
                    ),
                ],
            )
        );

        $item = $updated->items->first();

        $this->assertSame(4, $item->quantity);
        $this->assertEquals(800.00, (float) $item->unit_price);
        $this->assertEquals(3200.00, (float) $item->estimated_cost);
        $this->assertEquals(3200.00, (float) $updated->total_estimated_amount);
    }
}
