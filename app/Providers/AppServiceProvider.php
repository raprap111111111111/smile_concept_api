<?php

namespace App\Providers;

use App\Domain\Auth\Repositories\UserRepository;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        // `artisan serve` blanks every environment variable that is not on its
        // passthrough allowlist. On Windows that includes TMP/TEMP, which PHP
        // needs to create the temporary file for an uploaded file — without
        // them every multipart upload dies at request startup with
        // "File upload error - unable to create a temporary file".
        if (PHP_OS_FAMILY === 'Windows') {
            ServeCommand::$passthroughVariables[] = 'TMP';
            ServeCommand::$passthroughVariables[] = 'TEMP';
        }

        // Super-admin bypass
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        // ─── Token Lifetimes ──────────────────────────────────────
        if (app()->environment('production')) {
            // Production: short-lived access, long-lived refresh
            Passport::tokensExpireIn(now()->addMinutes(60));           // 1 hour
            Passport::refreshTokensExpireIn(now()->addDays(30));       // 30 days
            Passport::personalAccessTokensExpireIn(now()->addDays(7)); // 7 days
        } else {
            // Development: long lifetimes so you don't get logged out
            Passport::tokensExpireIn(now()->addYear());
            Passport::refreshTokensExpireIn(now()->addYears(2));
            Passport::personalAccessTokensExpireIn(now()->addYear());
        }
    }
}