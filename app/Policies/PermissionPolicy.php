<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('permission.viewAny');
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->can('permission.view');
    }

    public function create(User $user): bool
    {
        return $user->can('permission.create');
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->can('permission.update');
    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->can('permission.delete');
    }
}