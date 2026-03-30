<?php

use App\Actions\Stripe\HandleConnectAccountUpdated;
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
