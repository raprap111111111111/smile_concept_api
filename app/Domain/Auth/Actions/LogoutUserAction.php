<?php

namespace App\Domain\Auth\Actions;

use Illuminate\Http\Request;

class LogoutUserAction
{
    /**
     * Revoke the current token the user is logged in with.
     */
    public function execute(Request $request): void
    {
        // Delete the specific token instance used for this request
        $request->user()->currentAccessToken()->delete();
    }
}