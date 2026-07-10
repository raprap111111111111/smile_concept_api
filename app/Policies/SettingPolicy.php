<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('setting.view');
    }

    public function view(User $user, Setting $setting): bool
    {
        // Public settings can be viewed by anyone (checked in controller instead)
        return $user->can('setting.view');
    }

    /**
     * Settings are seeded — not manually created.
     */
    public function create(User $user): bool
    {
        return $user->can('setting.update');
    }

    public function update(User $user, Setting $setting): bool
    {
        // Prevent editing locked settings even if user has permission
        if (!$setting->is_editable) {
            return false;
        }

        return $user->can('setting.update');
    }

    /**
     * Settings should never be deleted, only disabled/updated.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return false;
    }
}