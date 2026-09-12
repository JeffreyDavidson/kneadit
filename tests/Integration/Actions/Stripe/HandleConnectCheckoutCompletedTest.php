<?php

use App\Actions\Stripe\HandleConnectCheckoutCompleted;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;
use JMac\Testing\Double;

beforeEach(fn () => setUpCentralTest());

test('it does nothing when session has no id', function () {
    $session = ['id' => null, 'metadata' => []];

    resolve(HandleConnectCheckoutCompleted::class)($session);

    expect(true)->toBeTrue();
});

test('it does nothing when tenant id is missing from metadata', function () {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'missing tenant_id'));

    $session = [
        'id' => 'cs_test_456',
        'metadata' => ['order_id' => 1],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});

test('it does nothing when tenant is not found', function () {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Tenant not found'));

    $session = [
        'id' => 'cs_test_789',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'nonexistent-tenant',
        ],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});

test('it processes checkout session within tenant context', function () {
    $tenant = createTenant(['id' => 'checkout-tenant', 'email' => 'checkout@test.com']);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->resolves(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('info')->andReturnNull();

    $session = [
        'id' => 'cs_test_success',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'checkout-tenant',
        ],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});

test('it catches exceptions during tenant context processing', function () {
    $tenant = createTenant(['id' => 'error-tenant', 'email' => 'error@test.com']);

    $tenancyManager = Double::for(TenancyManager::class);
    $tenancyManager->expects('withinTenant')
        ->throws(new Exception('Processing failed'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Error processing checkout session'));

    $session = [
        'id' => 'cs_test_error',
        'metadata' => [
            'order_id' => 1,
            'tenant_id' => 'error-tenant',
        ],
    ];

    resolve(HandleConnectCheckoutCompleted::class)($session);
});
