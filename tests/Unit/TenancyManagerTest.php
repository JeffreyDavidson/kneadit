<?php

use App\Models\Tenant;
use App\Services\TenancyManager;
use Stancl\Tenancy\Tenancy;

beforeEach(function () {
    $this->tenancy = Mockery::mock(Tenancy::class);
    $this->app->instance(Tenancy::class, $this->tenancy);
});

it('initializes tenancy, runs callback, and ends tenancy', function () {
    $tenant = Mockery::mock(Tenant::class);
    $callbackExecuted = false;

    $this->tenancy->shouldReceive('initialize')->with($tenant)->once();
    $this->tenancy->shouldReceive('end')->once();

    $manager = new TenancyManager;
    $result = $manager->withinTenant($tenant, function () use (&$callbackExecuted) {
        $callbackExecuted = true;

        return 'result';
    });

    expect($callbackExecuted)->toBeTrue();
    expect($result)->toBe('result');
});

it('ends tenancy even when callback throws an exception', function () {
    $tenant = Mockery::mock(Tenant::class);

    $this->tenancy->shouldReceive('initialize')->with($tenant)->once();
    $this->tenancy->shouldReceive('end')->once();

    $manager = new TenancyManager;

    expect(fn () => $manager->withinTenant($tenant, function () {
        throw new RuntimeException('Test error');
    }))->toThrow(RuntimeException::class, 'Test error');
});

it('passes the tenant to the callback', function () {
    $tenant = Mockery::mock(Tenant::class);
    $receivedTenant = null;

    $this->tenancy->shouldReceive('initialize')->with($tenant)->once();
    $this->tenancy->shouldReceive('end')->once();

    $manager = new TenancyManager;
    $manager->withinTenant($tenant, function (Tenant $t) use (&$receivedTenant) {
        $receivedTenant = $t;
    });

    expect($receivedTenant)->toBe($tenant);
});
