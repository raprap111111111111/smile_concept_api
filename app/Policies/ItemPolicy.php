<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('inventory.viewAny');
    }

    public function view(User $user, Item $item): bool
    {
        return $user->can('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.create');
    }

    public function update(User $user, Item $item): bool
    {
        return $user->can('inventory.update');
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->can('inventory.delete');
    }
}