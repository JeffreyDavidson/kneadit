<?php

use App\Http\Middleware\BrowserTestAwareThrottle;
use App\Http\Middleware\EnsureOrderAccess;
use App\Http\Middleware\EnsureSubscribed;
use App\Http\Middleware\InitializeTenancyIfNeeded;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetActorContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        // The cart_token is a ULID that's already unguessable; encrypting it
        // adds no security and breaks cookie reattach when the test client
        // writes a raw value.
        $middleware->encryptCookies(except: [
            'cart_token',
        ]);

        // Add security headers to all web responses
        $middleware->web(append: [
            SecurityHeaders::class,
            SetActorContext::class,
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
            'order.access' => EnsureOrderAccess::class,
            // Bypass rate limits for the local browser-test tenant so Pest browser
            // form tests don't 429 mid-flow; production traffic still throttles.
            'throttle' => BrowserTestAwareThrottle::class,
        ]);

        $middleware->redirectTo(guests: '/login', users: '/billing/plans');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();

        // Forward unhandled exceptions to Sentry. No-op when SENTRY_LARAVEL_DSN
        // is unset (local/test) so this is safe to leave wired up everywhere.
        Integration::handles($exceptions);

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // JSON:API validation errors: one error entry per field-message pair, source pointer points at the attribute.
        $exceptions->renderable(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'errors' => collect($e->errors())
                    ->flatMap(fn (array $messages, string $field) => collect($messages)->map(fn (string $message) => [
                        'status' => '422',
                        'title' => 'Validation Error',
                        'detail' => $message,
                        'source' => ['pointer' => "/data/attributes/{$field}"],
                    ]))
                    ->values()
                    ->all(),
            ], 422, ['Content-Type' => 'application/vnd.api+json']);
        });

        // Generic fallback for any other exception on /api/*.
        $exceptions->renderable(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            return response()->json([
                'errors' => [[
                    'status' => (string) $status,
                    'title' => Response::$statusTexts[$status] ?? 'Error',
                    'detail' => $e->getMessage() ?: null,
                ]],
            ], $status, ['Content-Type' => 'application/vnd.api+json']);
        });
    })->create();
