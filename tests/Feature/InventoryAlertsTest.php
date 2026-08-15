<?php

namespace Tests\Feature;

use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Domain\Settings\Services\SettingService;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use App\Notifications\StockAlertDigestNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryAlertsTest extends TestCase
{
    use RefreshDatabase;

    /** 08:00 — the default inventory_low_stock_alert_hour. */
    private const NOW = '2026-08-19 08:00:00';

    private Branch $main;
    private Branch $satellite;
    private Item $anesthetic;
    private User $keeper;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));
        Notification::fake();

        Permission::create(['name' => 'inventory.viewAny', 'guard_name' => 'api']);

        $this->main = Branch::create([
            'name' => 'Main', 'branch_code' => 'MAIN', 'address' => '1 Test St',
        ]);
        $this->satellite = Branch::create([
            'name' => 'Satellite', 'branch_code' => 'SAT', 'address' => '2 Test St',
        ]);

        $this->anesthetic = Item::create([
            'name' => 'Lidocaine 2% carpule', 'sku' => 'ANES-LIDO-2',
            'category' => 'Anesthetics', 'unit_of_measure' => 'carpule',
            'minimum_threshold' => 20,
        ]);

        $this->keeper = User::factory()->create();
        $this->keeper->givePermissionTo('inventory.viewAny');
        $this->keeper->branches()->attach($this->main->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_a_branch_below_its_reorder_point_gets_a_digest(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertSentTo(
            $this->keeper,
            StockAlertDigestNotification::class,
            function (StockAlertDigestNotification $n): bool {
                return $n->branch->id === $this->main->id
                    && count($n->lowStock) === 1
                    && $n->lowStock[0]['quantity'] === 5
                    && $n->lowStock[0]['threshold'] === 20;
            },
        );
    }

    public function test_healthy_stock_produces_no_digest(): void
    {
        $this->stockIn($this->main->id, 500, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_an_expiring_batch_is_flagged_even_when_stock_is_healthy(): void
    {
        // Well above the reorder point, but expiring inside the 30-day window.
        $this->stockIn($this->main->id, 500, Carbon::parse(self::NOW)->addDays(10)->toDateString());

        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertSentTo(
            $this->keeper,
            StockAlertDigestNotification::class,
            function (StockAlertDigestNotification $n): bool {
                return $n->lowStock === [] && count($n->expiring) === 1;
            },
        );
    }

    public function test_the_cooldown_suppresses_a_repeat(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();
        Notification::assertSentTimes(StockAlertDigestNotification::class, 1);

        // Same hour next day, still low, still inside the 3-day cooldown.
        Carbon::setTestNow(Carbon::parse(self::NOW)->addDay());
        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertSentTimes(StockAlertDigestNotification::class, 1);
    }

    public function test_the_cooldown_expires(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();

        Carbon::setTestNow(Carbon::parse(self::NOW)->addDays(4));
        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertSentTimes(StockAlertDigestNotification::class, 2);
    }

    public function test_the_stamp_is_written_before_notifying(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();

        // Queued notifications mean a crash between send and save would
        // re-alert every run, so the stamp lands first.
        $this->assertNotNull(
            Inventory::where('branch_id', $this->main->id)->sole()->last_low_stock_alert_at
        );
    }

    public function test_nothing_is_sent_outside_the_configured_hour(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        Carbon::setTestNow(Carbon::parse('2026-08-19 15:00:00'));

        // The scheduler runs this hourly; the command decides whether this is
        // the hour, because the setting is admin-tunable.
        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_force_ignores_the_hour(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        Carbon::setTestNow(Carbon::parse('2026-08-19 15:00:00'));

        $this->artisan('inventory:check-levels', ['--force' => true])->assertSuccessful();

        Notification::assertSentTimes(StockAlertDigestNotification::class, 1);
    }

    public function test_dry_run_sends_nothing_and_leaves_no_stamp(): void
    {
        $this->stockIn($this->main->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels', ['--dry-run' => true])
            ->expectsOutputToContain('[dry-run]')
            ->assertSuccessful();

        Notification::assertNothingSent();
        $this->assertNull(
            Inventory::where('branch_id', $this->main->id)->sole()->last_low_stock_alert_at
        );
    }

    public function test_the_kill_switch_stops_everything(): void
    {
        app(SettingService::class)->set('inventory_low_stock_alert_enabled', false);

        $this->stockIn($this->main->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_a_digest_only_reaches_that_branch(): void
    {
        $this->stockIn($this->satellite->id, 5, '2028-01-01');

        $this->artisan('inventory:check-levels')->assertSuccessful();

        // The keeper works at Main and cannot restock Satellite's cupboard.
        Notification::assertNothingSent();
    }

    private function stockIn(int $branchId, int $quantity, string $expiry): void
    {
        app(StockLedger::class)->record(new RecordMovementDTO(
            branchId: $branchId,
            itemId: $this->anesthetic->id,
            type: StockMovementType::STOCK_IN,
            quantityDelta: $quantity,
            expiryDate: $expiry,
        ));
    }
}
