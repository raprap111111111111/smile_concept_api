<?php

namespace App\Policies;

use App\Domain\Branch\Services\BranchScope;
use App\Models\Inventory;
use App\Models\User;

/**
 * Two questions, both of which have to pass: does this user hold the
 * permission, and is this row at a branch they work at?
 *
 * Only the first was ever asked. view/update/delete took an $inventory and
 * ignored it entirely, so anyone with `inventory.update` could edit another
 * branch's stock — or re-point the row at a branch they had nothing to do with.
 */
class InventoryPolicy
{
    public function __construct(
        private readonly BranchScope $branchScope,
    ) {}

    public function viewAny(User $user): bool
    {
        // No row to place yet. InventoryRepository::paginate() applies the
        // branch restriction to the listing itself.
        return $user->can('inventory.viewAny');
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $user->can('inventory.view')
            && $this->branchScope->canAccess($user, $inventory->branch_id);
    }

    public function create(User $user): bool
    {
        // The target branch arrives in the request body, so StoreInventoryRequest
        // checks it — a policy with no model cannot.
        return $user->can('inventory.create');
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->can('inventory.update')
            && $this->branchScope->canAccess($user, $inventory->branch_id);
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->can('inventory.delete')
            && $this->branchScope->canAccess($user, $inventory->branch_id);
    }

    // ── Stock movements ───────────────────────────────
    //
    // These four permissions have been seeded and granted to admin since the
    // beginning with nothing behind them. The branch half of each check lives
    // in the corresponding FormRequest, since the branch is a body parameter
    // rather than a routed model.

    public function stockIn(User $user): bool
    {
        return $user->can('inventory.stock-in');
    }

    public function stockOut(User $user): bool
    {
        return $user->can('inventory.stock-out');
    }

    public function adjust(User $user): bool
    {
        return $user->can('inventory.adjust');
    }

    public function transfer(User $user): bool
    {
        return $user->can('inventory.transfer');
    }
}
