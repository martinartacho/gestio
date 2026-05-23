<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // Cancel·la matrícules manuals pendents que han superat el termini (execució diària a les 08:00)
        $schedule->command('enrollments:expire')->dailyAt('08:00');
        // Recordatori 1 hora abans de la caducitat (execució cada 15 min)
        $schedule->command('enrollments:remind')->everyFifteenMinutes();
    })
    ->withProviders([
        App\Providers\Filament\AdminPanelProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'feature' => \App\Http\Middleware\FeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();