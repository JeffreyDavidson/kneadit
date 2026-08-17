<?php

use App\Http\Middleware\EnsureOnboardingComplete;
use App\Models\Platform\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function initializeTenantForOnboardingMiddleware(): Tenant
{
    $tenant = Tenant::query()->make([
        'id' => 'onboarding-middleware-test',
        'name' => 'Test Bakery',
        'email' => 'owner@example.com',
    ]);

    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);

    return $tenant;
}

test('passes through when no tenant is initialized', function () {
    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for unauthenticated users', function () {
    initializeTenantForOnboardingMiddleware();

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for auth routes', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/auth/login');
    $request->setRouteResolver(fn () => (new Illuminate\Routing\Route('GET', '/admin/auth/login', []))->name('filament.admin.auth.login'));

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through when already on onboarding page', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/onboarding');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for livewire update requests', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/livewire/update');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for livewire hashed paths', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/livewire-abc123');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through when onboarding is complete', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $settings = makeTenantSettings(
        onboarding: new App\DataTransferObjects\Settings\OnboardingSettings(completedAt: now()->toDateTimeString()),
    );
    app()->instance(App\Services\Settings\TenantSettings::class, $settings);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through gracefully when TenantSettings throws exception', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    app()->bind(App\Services\Settings\TenantSettings::class, function () {
        throw new RuntimeException('Settings unavailable');
    });

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects to onboarding when onboardingCompletedAt is null using TenantSettings', function () {
    initializeTenantForOnboardingMiddleware();

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $settings = makeTenantSettings(
        onboarding: new App\DataTransferObjects\Settings\OnboardingSettings(completedAt: null),
    );
    app()->instance(App\Services\Settings\TenantSettings::class, $settings);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/dashboard');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toContain('onboarding');
});
