<?php

use App\Http\Middleware\EnsureSubscribed;
use App\Http\Middleware\InitializeTenancyIfNeeded;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestForgery(except: [
            'stripe/*',
        ]);

        // Add security headers to all web responses
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        // Initialize tenancy on ALL web requests (including Livewire updates)
        // This ensures tenant DB is active before auth/session checks
        // Must be prepended so it runs BEFORE StartSession (which uses DB)
        // Only runs on non-central domains (subdomains)
        $middleware->web(prepend: [
            InitializeTenancyIfNeeded::class,
        ]);

        $middleware->alias([
            'subscribed' => EnsureSubscribed::class,
        ]);

        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/billing/plans');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
