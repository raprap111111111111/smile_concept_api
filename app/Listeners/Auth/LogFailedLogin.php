<?php

namespace App\Listeners\Auth;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function __construct(
        private readonly ActivityLogger $logger
    ) {}

    public function handle(Failed $event): void
    {
        $this->logger->logEvent(
            'login_failed',
            [
                'email'  => $event->credentials['email'] ?? 'unknown',
                'guard'  => $event->guard,
                'reason' => 'invalid_credentials',
            ],
            $event->user?->id
        );
    }
}