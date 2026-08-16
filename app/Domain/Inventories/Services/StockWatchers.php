<?php

namespace App\Domain\Inventories\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Answers "who at this branch should hear about stock problems?".
 *
 * Staff assigned to the branch (pivot or legacy branch_id column) who can see
 * inventory — so alerts reach someone who can actually restock, rather than
 * every admin in the system.
 */
class StockWatchers
{
    /** @return Collection<int, User> */
    public function at(int $branchId): Collection
    {
        return User::query()
            ->where(function ($query) use ($branchId): void {
                $query->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
                    ->orWhere('branch_id', $branchId);
            })
            ->get()
            ->filter(fn (User $user): bool => $user->can('inventory.viewAny'))
            ->values();
    }
}
