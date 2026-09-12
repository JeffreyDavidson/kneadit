<?php

use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JMac\Testing\Double;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('passes through when no tenant is initialized', function () {
    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for unauthenticated users', function () {
    // Bind a fake tenant so tenant() returns truthy
    app()->instance(TenantContract::class, Double::for(TenantContract::class));

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for auth routes', function () {
    $tenant = Double::for(TenantContract::class);
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/auth/login');
    $request->setRouteResolver(fn () => (new Illuminate\Routing\Route('GET', '/admin/auth/login', []))->name('filament.admin.auth.login'));

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through when already on onboarding page', function () {
    $tenant = Double::for(TenantContract::class);
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/admin/onboarding');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for livewire update requests', function () {
    $tenant = Double::for(TenantContract::class);
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/livewire/update');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through for livewire hashed paths', function () {
    $tenant = Double::for(TenantContract::class);
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $user = App\Models\Staff\User::factory()->create();
    actingAs($user);

    $middleware = new EnsureOnboardingComplete;
    $request = Request::create('/livewire-abc123');

    $response = $middleware->handle($request, fn () => new Response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('passes through when onboarding is complete', function () {
    $tenant = Double::for(TenantContract::class);
    $tenant->allows('getTenantKey')->returns('test-bakery');
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

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
    $tenant = Double::for(TenantContract::class);
    $tenant->allows('getTenantKey')->returns('test-bakery');
    app()->instance(TenantContract::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

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
    $tenant = new App\Models\Platform\Tenant(['id' => 'onboarding-bakery']);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);

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
