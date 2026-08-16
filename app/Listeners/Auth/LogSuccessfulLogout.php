<?php

namespace App\Listeners\Auth;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function __construct(
        private readonly ActivityLogger $logger
    ) {}

    public function handle(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        $this->logger->logEvent(
            'logged_out',
            [
                'email' => $event->user->email,
                'name'  => $event->user->name,
                'guard' => $event->guard,
            ],
            $event->user->id
        );
    }
}