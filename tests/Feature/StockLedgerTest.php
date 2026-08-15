<?php

namespace Tests\Feature;

use App\Domain\Inventories\DTOs\RecordMovementDTO;
use App\Domain\Inventories\Services\StockLedger;
use App\Enums\StockMovementType;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\InventoryBatch;
use App\Models\Item;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The ledger is the foundation everything else in inventory sits on, so these
 * cover the rules that are expensive to get wrong: FEFO ordering, the shortfall
 * path that must never block clinical work, and the invariant that the summary
 * quantity always equals the sum of the movements.
 */
class StockLedgerTest extends TestCase
{
    use RefreshDatabase;

    private const NOW = '2026-08-19 08:00:00';

    private Branch $branch;
    private Item $item;
    private StockLedger $ledger;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse(self::NOW));

        $this->branch = Branch::create([
            'name'        => 'Main',
            'branch_code' => 'MAIN',
            'address'     => '123 Test St',
        ]);

        $this->item = Item::create([
            'name'              => 'Lidocaine 2% carpule',
            'sku'               => 'ANES-LIDO-2',
            'category'          => 'Anesthetics',
            'unit_of_measure'   => 'carpule',
            'minimum_threshold' => 20,
        ]);

        $this->ledger = app(StockLedger::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ── Inflow ────────────────────────────────────────

    public function test_stock_in_opens_a_batch_and_sets_the_aggregate(): void
    {
        $result = $this->stockIn(50, expiry: '2027-02-01', lot: 'A1');

        $this->assertSame(50, $result->balanceAfter);
        $this->assertFalse($result->hasShortfall());
        $this->assertCount(1, $result->movements);

        $batch = InventoryBatch::sole();
        $this->assertSame('A1', $batch->lot_number);
        $this->assertSame(50, $batch->quantity_received);
        $this->assertSame(50, $batch->quantity_remaining);

        $inventory = Inventory::sole();
        $this->assertSame(50, $inventory->quantity);
        $this->assertSame('2027-02-01', $inventory->expiry_date->toDateString());
    }

    public function test_the_aggregate_expiry_tracks_the_earliest_open_batch(): void
    {
        $this->stockIn(50, expiry: '2027-06-01', lot: 'LATE');
        $this->stockIn(30, expiry: '2026-10-01', lot: 'SOON');

        // Two lots of one item at one branch — the thing the old single
        // expiry_date column could not represent at all.
        $this->assertSame(2, InventoryBatch::count());

        $inventory = Inventory::sole();
        $this->assertSame(80, $inventory->quantity);
        $this->assertSame('2026-10-01', $inventory->expiry_date->toDateString());
    }

    // ── FEFO ──────────────────────────────────────────

    public function test_consumption_draws_from_the_earliest_expiring_batch_first(): void
    {
        // Stocked in reverse order on purpose: FEFO must beat insertion order.
        $this->stockIn(10, expiry: '2027-06-01', lot: 'LATE');
        $this->stockIn(10, expiry: '2026-10-01', lot: 'SOON');

        $result = $this->consume(6);

        $this->assertFalse($result->hasShortfall());
        $this->assertSame(14, $result->balanceAfter);

        $soon = InventoryBatch::where('lot_number', 'SOON')->sole();
        $late = InventoryBatch::where('lot_number', 'LATE')->sole();

        $this->assertSame(4, $soon->quantity_remaining, 'SOON expires first, so it drains first');
        $this->assertSame(10, $late->quantity_remaining, 'LATE must be untouched');
    }

    public function test_consumption_spans_batches_and_writes_one_movement_each(): void
    {
        $this->stockIn(4, expiry: '2026-10-01', lot: 'SOON');
        $this->stockIn(10, expiry: '2027-06-01', lot: 'LATE');

        $result = $this->consume(6);

        $this->assertCount(2, $result->movements, 'one row per lot touched');
        $this->assertSame(8, $result->balanceAfter);

        $this->assertSame(0, InventoryBatch::where('lot_number', 'SOON')->sole()->quantity_remaining);
        $this->assertSame(8, InventoryBatch::where('lot_number', 'LATE')->sole()->quantity_remaining);

        // The trail names the lots, in the order they were drawn.
        $deltas = collect($result->movements)->pluck('quantity_delta')->all();
        $this->assertSame([-4, -2], $deltas);
    }

    public function test_batches_without_an_expiry_are_consumed_last(): void
    {
        // A non-perishable lot must not be burned ahead of one that expires,
        // or the perishable stock is wasted.
        $this->stockIn(10, expiry: null, lot: 'FOREVER');
        $this->stockIn(10, expiry: '2026-10-01', lot: 'SOON');

        $this->consume(10);

        $this->assertSame(0, InventoryBatch::where('lot_number', 'SOON')->sole()->quantity_remaining);
        $this->assertSame(10, InventoryBatch::where('lot_number', 'FOREVER')->sole()->quantity_remaining);
    }

    public function test_same_expiry_breaks_the_tie_on_oldest_received(): void
    {
        $this->stockIn(5, expiry: '2026-10-01', lot: 'FIRST');
        $this->stockIn(5, expiry: '2026-10-01', lot: 'SECOND');

        $this->consume(5);

        $this->assertSame(0, InventoryBatch::where('lot_number', 'FIRST')->sole()->quantity_remaining);
        $this->assertSame(5, InventoryBatch::where('lot_number', 'SECOND')->sole()->quantity_remaining);
    }

    // ── Shortfall ─────────────────────────────────────

    public function test_a_shortfall_is_recorded_rather_than_rejected(): void
    {
        $this->stockIn(4, expiry: '2026-10-01', lot: 'SOON');

        $result = $this->consume(6);

        $this->assertTrue($result->hasShortfall());
        $this->assertSame(2, $result->shortfall);
        $this->assertSame(-2, $result->balanceAfter, 'the gap shows as a negative balance');

        // Batches never go negative — the unmet part has no lot behind it.
        $this->assertSame(0, InventoryBatch::sole()->quantity_remaining);

        $shortfall = StockMovement::whereNull('inventory_batch_id')->sole();
        $this->assertSame(-2, $shortfall->quantity_delta);
        $this->assertTrue($shortfall->isShortfall());
    }

    public function test_consuming_with_no_stock_at_all_still_records(): void
    {
        $result = $this->consume(3);

        $this->assertSame(3, $result->shortfall);
        $this->assertSame(-3, $result->balanceAfter);
        $this->assertSame(-3, Inventory::sole()->quantity);
    }

    public function test_restocking_after_a_shortfall_recovers_the_balance(): void
    {
        $this->consume(3);
        $this->assertSame(-3, Inventory::sole()->quantity);

        $this->stockIn(10, expiry: '2027-01-01', lot: 'NEW');

        // 10 arrived against a 3 debt. The debt stays visible in the ledger but
        // the balance reflects it rather than double-counting.
        $this->assertSame(7, Inventory::sole()->quantity);
    }

    // ── Adjustment ────────────────────────────────────

    public function test_a_negative_adjustment_draws_down_stock(): void
    {
        $this->stockIn(10, expiry: '2027-01-01', lot: 'A');

        $result = $this->ledger->record(new RecordMovementDTO(
            branchId: $this->branch->id,
            itemId: $this->item->id,
            type: StockMovementType::ADJUSTMENT,
            quantityDelta: -3,
            reason: 'Counted 7 on the shelf.',
        ));

        $this->assertSame(7, $result->balanceAfter);
        $this->assertSame(7, InventoryBatch::sole()->quantity_remaining);
    }

    public function test_a_zero_delta_writes_nothing(): void
    {
        $this->stockIn(10, expiry: '2027-01-01', lot: 'A');
        $before = StockMovement::count();

        $result = $this->ledger->record(new RecordMovementDTO(
            branchId: $this->branch->id,
            itemId: $this->item->id,
            type: StockMovementType::ADJUSTMENT,
            quantityDelta: 0,
            reason: 'Count matched.',
        ));

        $this->assertSame([], $result->movements);
        $this->assertSame(10, $result->balanceAfter);
        $this->assertSame($before, StockMovement::count(), 'a no-op must not pad the ledger');
    }

    // ── The invariant ─────────────────────────────────

    public function test_the_aggregate_always_equals_the_sum_of_movements(): void
    {
        $this->stockIn(50, expiry: '2027-06-01', lot: 'A');
        $this->stockIn(20, expiry: '2026-10-01', lot: 'B');
        $this->consume(30);
        $this->consume(5);
        $this->stockIn(10, expiry: '2027-09-01', lot: 'C');
        $this->consume(100);

        $ledgerSum = (int) StockMovement::sum('quantity_delta');

        $this->assertSame($ledgerSum, Inventory::sole()->quantity);
        $this->assertSame(-55, $ledgerSum);
    }

    public function test_every_movement_carries_the_running_balance(): void
    {
        $this->stockIn(10, expiry: '2027-01-01', lot: 'A');
        $this->consume(4);
        $this->consume(3);

        $balances = StockMovement::orderBy('id')->pluck('balance_after')->all();

        $this->assertSame([10, 6, 3], $balances);
    }

    // ── Helpers ───────────────────────────────────────

    private function stockIn(int $quantity, ?string $expiry, ?string $lot = null)
    {
        return $this->ledger->record(new RecordMovementDTO(
            branchId: $this->branch->id,
            itemId: $this->item->id,
            type: StockMovementType::STOCK_IN,
            quantityDelta: $quantity,
            lotNumber: $lot,
            expiryDate: $expiry,
        ));
    }

    private function consume(int $quantity)
    {
        return $this->ledger->record(new RecordMovementDTO(
            branchId: $this->branch->id,
            itemId: $this->item->id,
            type: StockMovementType::CONSUMPTION,
            quantityDelta: -$quantity,
        ));
    }
}
