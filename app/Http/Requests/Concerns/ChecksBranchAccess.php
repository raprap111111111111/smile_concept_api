<?php

namespace App\Http\Requests\Concerns;

use App\Domain\Branch\Services\BranchScope;

/**
 * Branch check for requests that name their branch in the body rather than the
 * route, which a policy cannot reach.
 *
 * Called from authorize(), so a branch the user does not work at comes back 403
 * rather than 422. That ordering also means a *nonexistent* branch id is a 403
 * instead of a validation error — which is the right trade: telling someone
 * which branch ids exist is not information they are entitled to.
 */
trait ChecksBranchAccess
{
    protected function canAccessBranch(mixed $branchId): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return app(BranchScope::class)->canAccess(
            $user,
            is_numeric($branchId) ? (int) $branchId : null,
        );
    }
}
