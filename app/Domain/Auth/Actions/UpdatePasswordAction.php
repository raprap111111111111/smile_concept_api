<?php

namespace App\Domain\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordAction
{
    public function execute(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
    }
}