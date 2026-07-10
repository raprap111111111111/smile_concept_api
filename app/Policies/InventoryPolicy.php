<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventory.viewAny');
    }

    public function view(User $user, Inventory $inventory): bool
    {
        return $user->can('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.create');
    }

    public function update(User $user, Inventory $inventory): bool
    {
        return $user->can('inventory.update');
    }

    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->can('inventory.delete');
    }
}