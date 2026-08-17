<?php

use App\Actions\Stripe\HandleConnectAccountUpdated;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;

beforeEach(fn () => setUpCentralTest());

test('logs warning when tenant_id is missing from account metadata', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'missing tenant_id'));

    $account = [
        'id' => 'acct_test123',
        'charges_enabled' => true,
        'metadata' => [],
    ];

    resolve(HandleConnectAccountUpdated::class)($account);
});

test('logs warning when tenant is not found', function () {
    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Tenant not found'));

    $account = [
        'id' => 'acct_test123',
        'charges_enabled' => true,
        'metadata' => ['tenant_id' => 'nonexistent-tenant'],
    ];

    resolve(HandleConnectAccountUpdated::class)($account);
});

test('reads tenant_id from object metadata', function () {
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'missing tenant_id'));

    $account = (object) [
        'id' => 'acct_test123',
        'charges_enabled' => true,
        'metadata' => (object) [],
    ];

    resolve(HandleConnectAccountUpdated::class)($account);
});

test('updates tenant settings when tenant exists and charges enabled', function () {
    $tenant = createTenant(['id' => 'stripe-tenant', 'email' => 'stripe@test.com']);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    mockExpectation($tenancyManager, 'withinTenant')
        ->once()
        ->andReturnUsing(fn ($tenant, $callback) => $callback());

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('info')->andReturnNull();

    $account = [
        'id' => 'acct_test123',
        'charges_enabled' => true,
        'metadata' => ['tenant_id' => 'stripe-tenant'],
    ];

    resolve(HandleConnectAccountUpdated::class)($account);
});

test('logs error when tenant context callback throws exception', function () {
    $tenant = createTenant(['id' => 'error-tenant', 'email' => 'error@test.com']);

    $tenancyManager = Mockery::mock(TenancyManager::class);
    mockExpectation($tenancyManager, 'withinTenant')
        ->once()
        ->andThrow(new Exception('Connection failed'));

    app()->instance(TenancyManager::class, $tenancyManager);

    Log::shouldReceive('info')->andReturnNull();
    Log::shouldReceive('error')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'Failed to update tenant Stripe Connect'));

    $account = [
        'id' => 'acct_test123',
        'charges_enabled' => true,
        'metadata' => ['tenant_id' => 'error-tenant'],
    ];

    resolve(HandleConnectAccountUpdated::class)($account);
});
