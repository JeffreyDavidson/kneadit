<?php

use App\Http\Middleware\EnsureStorefrontEnabled;
use Illuminate\Http\Request;

test('middleware passes when no tenant context', function () {
    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getContent())->toBe('OK');
});

test('redirects to external website when storefront disabled and external url set', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    $tenant->storefront_enabled = false;
    $tenant->external_website = 'https://mybakery.com';

    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getStatusCode())->toBe(302)
        ->and($response->headers->get('Location'))->toBe('https://mybakery.com');
});

test('disabled storefront view receives storeName from TenantSettings', function () {
    $tenant = Mockery::mock(Stancl\Tenancy\Contracts\Tenant::class)->shouldIgnoreMissing();
    $tenant->storefront_enabled = false;
    $tenant->external_website = null;

    app()->instance(Stancl\Tenancy\Contracts\Tenant::class, $tenant);
    app()->bind('currentTenant', fn () => $tenant);

    $settings = makeTenantSettings(store: makeStoreInfo(['name' => 'Mock Bakery']));
    app()->instance(App\Services\Settings\TenantSettings::class, $settings);

    $middleware = new EnsureStorefrontEnabled;
    $request = Request::create('/');

    $response = $middleware->handle($request, fn () => response('OK'));

    expect($response->getContent())->toContain('Mock Bakery');
});
