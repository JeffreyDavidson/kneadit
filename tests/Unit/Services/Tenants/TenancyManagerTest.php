<?php

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use JMac\Testing\Double;
use Stancl\Tenancy\Tenancy;

beforeEach(function () {
    $this->tenancy = Double::for(Tenancy::class);
    $this->app->instance(Tenancy::class, $this->tenancy);
});

it('initializes tenancy, runs callback, and ends tenancy', function () {
    $tenant = new Tenant;
    $callbackExecuted = false;

    $this->tenancy->expects('initialize');
    $this->tenancy->expects('end');

    $manager = resolve(TenancyManager::class);
    $result = $manager->withinTenant($tenant, function () use (&$callbackExecuted) {
        $callbackExecuted = true;

        return 'result';
    });

    expect($callbackExecuted)->toBeTrue()->and($result)->toBe('result');
});

it('ends tenancy even when callback throws an exception', function () {
    $tenant = new Tenant;

    $this->tenancy->expects('initialize');
    $this->tenancy->expects('end');

    $manager = resolve(TenancyManager::class);

    expect(fn () => $manager->withinTenant($tenant, function () {
        throw new RuntimeException('Test error');
    }))->toThrow(RuntimeException::class, 'Test error');
});

it('passes the tenant to the callback', function () {
    $tenant = new Tenant;
    $receivedTenant = null;

    $this->tenancy->expects('initialize');
    $this->tenancy->expects('end');

    $manager = resolve(TenancyManager::class);
    $manager->withinTenant($tenant, function (Tenant $t) use (&$receivedTenant) {
        $receivedTenant = $t;
    });

    expect($receivedTenant)->toBe($tenant);
});
