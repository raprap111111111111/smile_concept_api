<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\User;

class InvoiceItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invoice.viewAny');   // Shares invoice permission
    }

    public function view(User $user, InvoiceItem $item): bool
    {
        return $user->can('invoice.view');
    }

    public function create(User $user): bool
    {
        return $user->can('invoice.update');
    }

    public function update(User $user, InvoiceItem $item): bool
    {
        return $user->can('invoice.update');
    }

    public function delete(User $user, InvoiceItem $item): bool
    {
        return $user->can('invoice.update');
    }
}