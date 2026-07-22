<?php

use App\Http\Middleware\Cors;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(Cors::class);

        // Spatie's guards are referenced by alias in the route files
        // (e.g. `permission:dashboard.view`); without these registrations
        // every guarded route dies with "Target class [permission] does not exist".
        $middleware->alias([
            'role'               => RoleMiddleware::class,
            'permission'         => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {

        // ─── Appointment Reminders ──────────────────────────
        // Sends pending reminders (email/SMS/push) as they become due
        $schedule->command('reminders:dispatch')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // ─── Overdue Invoice Notifications ──────────────────
        // Sends payment overdue notices daily (respects 7-day cooldown)
        $schedule->command('invoices:notify-overdue')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground();

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );

        // Business-rule violations (e.g. invalid status transition)
        // are client errors, not server errors
        $exceptions->render(function (\DomainException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return null;
        });
    })->create();