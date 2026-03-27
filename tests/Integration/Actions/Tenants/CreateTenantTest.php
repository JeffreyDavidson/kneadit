<?php

use App\Actions\Tenants\CreateTenant;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    setUpCentralTest();

    $this->user = User::query()->create([
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
});

afterEach(function () {
    @unlink(database_path('tenanttestbakery'));
});

it('creates a tenant with domain and seeds the tenant database', function () {
    $action = resolve(CreateTenant::class);

    $tenant = $action(
        user: $this->user,
        storeName: 'Test Bakery',
        subdomain: 'testbakery',
        useKneadItStorefront: true,
    );

    expect($tenant)->toBeInstanceOf(Tenant::class);
    expect($tenant->id)->toBe('testbakery');
    expect($tenant->store_name)->toBe('Test Bakery');
    expect($tenant->storefront_enabled)->toBeTrue();
    expect($tenant->is_active)->toBeTrue();
    expect($tenant->domains)->toHaveCount(1);
    expect($tenant->domains->first()->domain)->toBe('testbakery');
});
