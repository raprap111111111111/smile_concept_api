<?php

namespace App\Listeners\Auth;

use App\Domain\ActivityLogs\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;  

class LogSuccessfulLogin
{
    public function __construct(
        private readonly ActivityLogger $logger
    ) {}

    public function handle(Login $event): void 
    {
        $this->logger->logEvent(
            'logged_in',
            [
                'email' => $event->user->email,
                'name'  => $event->user->name,
                'guard' => $event->guard,
            ],
            $event->user->id
        );
    }
}