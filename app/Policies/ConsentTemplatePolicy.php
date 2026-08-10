<?php

namespace App\Policies;

use App\Models\ConsentTemplate;
use App\Models\User;

class ConsentTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('consent-form.viewAny')
            || $user->can('consent-form.sign'); 
    }

    public function view(User $user, ConsentTemplate $template): bool
    {
        return $user->can('consent-form.view')
            || $user->can('consent-form.viewAny')
            || $user->can('consent-form.sign');
    }

    public function create(User $user): bool
    {
        return $user->can('consent-form.create');
    }

    public function update(User $user, ConsentTemplate $template): bool
    {
        return $user->can('consent-form.update');
    }

    public function delete(User $user, ConsentTemplate $template): bool
    {
        return $user->can('consent-form.delete');
    }
}