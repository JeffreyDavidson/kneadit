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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);

        // Initialize tenancy on ALL web requests (including Livewire updates)
        // This ensures tenant DB is active before auth/session checks
        // Must be prepended so it runs BEFORE StartSession (which uses DB)
        // Only runs on non-central domains (subdomains)
        $middleware->web(prepend: [
            \App\Http\Middleware\InitializeTenancyIfNeeded::class,
        ]);

        $middleware->alias([
            'subscribed' => \App\Http\Middleware\EnsureSubscribed::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/billing/plans');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
