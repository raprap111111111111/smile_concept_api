<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__ . '/../routes/web.php',
        api:      __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
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
    })->create();