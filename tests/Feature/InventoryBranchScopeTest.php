<?php

namespace Tests\Feature;

use App\Domain\Branch\Services\BranchScope;
use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Branch isolation.
 *
 * Until now `branch_id` was only an optional query parameter, so every holder
 * of `inventory.viewAny` could read every branch's stock and every holder of
 * `inventory.update` could edit it — or re-point a row into a branch they had
 * nothing to do with. These tests pin the new behaviour down.
 */
class InventoryBranchScopeTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = '/api/v1';

    private Branch $north;
    private Branch $south;
    private Item $item;
    private Inventory $northStock;
    private Inventory $southStock;

    /** Works at North only. */
    private User $northStaff;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-19 08:00:00'));

        foreach ([
            'inventory.viewAny', 'inventory.view', 'inventory.create',
            'inventory.update', 'inventory.delete',
            'inventory.stock-in', 'inventory.stock-out',
            'inventory.adjust', 'inventory.transfer',
        ] as $name) {
            Permission::create(['name' => $name, 'guard_name' => 'api']);
        }

        $keeper = Role::create(['name' => 'stock-keeper', 'guard_name' => 'api']);
        $keeper->givePermissionTo(Permission::where('guard_name', 'api')->get());

        Role::create(['name' => 'super-admin', 'guard_name' => 'api']);

        $this->north = Branch::create(['name' => 'North', 'branch_code' => 'N', 'address' => '1 N St']);
        $this->south = Branch::create(['name' => 'South', 'branch_code' => 'S', 'address' => '2 S St']);

        $this->item = Item::create([
            'name' => 'Cotton roll', 'sku' => 'CON-COT-1', 'category' => 'Hygiene',
            'unit_of_measure' => 'piece', 'minimum_threshold' => 50,
        ]);

        $this->northStock = Inventory::create([
            'branch_id' => $this->north->id, 'item_id' => $this->item->id, 'quantity' => 100,
        ]);
        $this->southStock = Inventory::create([
            'branch_id' => $this->south->id, 'item_id' => $this->item->id, 'quantity' => 200,
        ]);

        $this->northStaff = User::factory()->create();
        $this->northStaff->assignRole($keeper);
        $this->northStaff->branches()->attach($this->north->id);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super-admin');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    // ── Reads ─────────────────────────────────────────

    public function test_the_listing_shows_only_the_users_branches(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        $response = $this->getJson(self::BASE . '/inventories')->assertOk();

        $this->assertSame(1, $response->json('data.total'));
        $this->assertSame($this->north->id, $response->json('data.records.0.branch_id'));
    }

    public function test_asking_for_another_branch_by_id_returns_nothing(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        // branch_id stays a legal filter — it narrows within your branches. It
        // must never widen past them.
        $this->getJson(self::BASE . '/inventories?branch_id=' . $this->south->id)
            ->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    public function test_opening_another_branches_row_directly_is_forbidden(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        $this->getJson(self::BASE . '/inventories/' . $this->southStock->id)->assertForbidden();
        $this->getJson(self::BASE . '/inventories/' . $this->northStock->id)->assertOk();
    }

    public function test_super_admin_sees_every_branch(): void
    {
        Passport::actingAs($this->superAdmin, ['*'], 'api');

        $this->getJson(self::BASE . '/inventories')
            ->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    public function test_a_user_attached_to_no_branch_sees_nothing(): void
    {
        $orphan = User::factory()->create();
        $orphan->assignRole('stock-keeper');

        Passport::actingAs($orphan, ['*'], 'api');

        // An empty allowlist must mean "nothing", never "everything".
        $this->getJson(self::BASE . '/inventories')
            ->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    public function test_the_primary_branch_is_the_fallback_when_the_pivot_is_empty(): void
    {
        $user = User::factory()->create(['branch_id' => $this->south->id]);
        $user->assignRole('stock-keeper');

        Passport::actingAs($user, ['*'], 'api');

        $this->getJson(self::BASE . '/inventories')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.records.0.branch_id', $this->south->id);
    }

    public function test_the_ledger_is_scoped_like_the_stock_it_describes(): void
    {
        Passport::actingAs($this->superAdmin, ['*'], 'api');
        $this->postJson(self::BASE . '/inventories/stock-in', [
            'branch_id' => $this->south->id, 'item_id' => $this->item->id, 'quantity' => 10,
        ])->assertCreated();

        Passport::actingAs($this->northStaff, ['*'], 'api');

        // Otherwise the ledger is a side door onto another branch's history.
        $this->getJson(self::BASE . '/stock-movements')
            ->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    // ── Writes ────────────────────────────────────────

    public function test_stock_verbs_are_refused_for_another_branch(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        $body = ['branch_id' => $this->south->id, 'item_id' => $this->item->id, 'quantity' => 5];

        $this->postJson(self::BASE . '/inventories/stock-in', $body)->assertForbidden();
        $this->postJson(self::BASE . '/inventories/usage', $body)->assertForbidden();

        $this->postJson(self::BASE . '/inventories/adjust', [
            'branch_id' => $this->south->id, 'item_id' => $this->item->id,
            'counted_quantity' => 5, 'reason' => 'x',
        ])->assertForbidden();
    }

    public function test_transfer_requires_access_to_both_ends(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        // Holds North but not South, so neither direction is allowed.
        $this->postJson(self::BASE . '/inventories/transfer', [
            'from_branch_id' => $this->north->id,
            'to_branch_id' => $this->south->id,
            'item_id' => $this->item->id,
            'quantity' => 1,
        ])->assertForbidden();

        $this->postJson(self::BASE . '/inventories/transfer', [
            'from_branch_id' => $this->south->id,
            'to_branch_id' => $this->north->id,
            'item_id' => $this->item->id,
            'quantity' => 1,
        ])->assertForbidden();
    }

    public function test_a_row_cannot_be_repointed_into_an_inaccessible_branch(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        // The row itself is theirs; the destination is not. This is the hole
        // that let someone move stock out of their own reach.
        $this->putJson(self::BASE . '/inventories/' . $this->northStock->id, [
            'branch_id' => $this->south->id,
        ])->assertForbidden();

        $this->assertSame(
            $this->north->id,
            $this->northStock->fresh()->branch_id,
        );
    }

    public function test_editing_another_branches_row_is_forbidden(): void
    {
        Passport::actingAs($this->northStaff, ['*'], 'api');

        $this->putJson(self::BASE . '/inventories/' . $this->southStock->id, [
            'quantity' => 1,
        ])->assertForbidden();

        $this->deleteJson(self::BASE . '/inventories/' . $this->southStock->id)->assertForbidden();

        $this->assertSame(200, $this->southStock->fresh()->quantity);
    }

    // ── The branch picker ─────────────────────────────

    public function test_branches_are_unscoped_by_default(): void
    {
        Permission::findOrCreate('branch.viewAny', 'api');
        $this->northStaff->givePermissionTo('branch.viewAny');

        Passport::actingAs($this->northStaff, ['*'], 'api');

        // Booking and scheduling pickers legitimately span every branch, and
        // several roles carry no branch_user rows — forcing the scope here
        // would empty those dropdowns system-wide.
        $this->getJson(self::BASE . '/branches')
            ->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    public function test_mine_narrows_the_picker_to_the_users_branches(): void
    {
        Permission::findOrCreate('branch.viewAny', 'api');
        $this->northStaff->givePermissionTo('branch.viewAny');

        Passport::actingAs($this->northStaff, ['*'], 'api');

        $response = $this->getJson(self::BASE . '/branches?mine=1')->assertOk();

        $this->assertSame(1, $response->json('data.total'));
        $this->assertSame($this->north->id, $response->json('data.records.0.id'));
    }

    public function test_mine_leaves_super_admin_unrestricted(): void
    {
        Passport::actingAs($this->superAdmin, ['*'], 'api');

        $this->getJson(self::BASE . '/branches?mine=1')
            ->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    public function test_mine_returns_nothing_for_a_user_attached_to_no_branch(): void
    {
        Permission::findOrCreate('branch.viewAny', 'api');

        $orphan = User::factory()->create();
        $orphan->givePermissionTo('branch.viewAny');

        Passport::actingAs($orphan, ['*'], 'api');

        // Honest: they cannot read or write stock at any branch, so offering
        // one in a picker would only lead to an empty list and a 403.
        $this->getJson(self::BASE . '/branches?mine=1')
            ->assertOk()
            ->assertJsonPath('data.total', 0);
    }

    // ── The resolver itself ───────────────────────────

    public function test_the_pivot_wins_over_the_primary_branch(): void
    {
        $user = User::factory()->create(['branch_id' => $this->south->id]);
        $user->branches()->attach($this->north->id);

        $scope = app(BranchScope::class);

        // users.branch_id is only a default for forms and a fallback for an
        // empty pivot — it is not a membership.
        $this->assertSame([$this->north->id], $scope->readableBranchIds($user));
        $this->assertTrue($scope->canAccess($user, $this->north->id));
        $this->assertFalse($scope->canAccess($user, $this->south->id));
    }

    public function test_super_admin_is_unrestricted_rather_than_broadly_listed(): void
    {
        $scope = app(BranchScope::class);

        // null means "no restriction". Distinct from [], which means "nothing".
        $this->assertNull($scope->readableBranchIds($this->superAdmin));
        $this->assertTrue($scope->canAccess($this->superAdmin, $this->south->id));
    }

    public function test_a_null_branch_is_never_accessible(): void
    {
        $scope = app(BranchScope::class);

        $this->assertFalse($scope->canAccess($this->northStaff, null));
    }
}
