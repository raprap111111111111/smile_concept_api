<?php

namespace App\Domain\Auth\Actions;

use Illuminate\Auth\Events\Logout; 
use Illuminate\Http\Request;

class LogoutUserAction
{
    /**
     * Revoke the current token the user is logged in with.
     */
    public function execute(Request $request): void
    {
        $user = $request->user();

        if ($user) {
            event(new Logout('api', $user));
        }

        // Then delete the specific token
        $user?->currentAccessToken()?->delete();
    }
}