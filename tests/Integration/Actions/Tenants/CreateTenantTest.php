<?php

use App\Actions\Tenants\CreateTenant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();

    $this->user = User::factory()->owner()->create([
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

    expect($tenant)->toBeInstanceOf(Tenant::class)->and($tenant->id)->toBe('testbakery')->and($tenant->store_name)->toBe('Test Bakery')->and($tenant->storefront_enabled)->toBeTrue()->and($tenant->is_active)->toBeTrue()->and($tenant->domains)->toHaveCount(1)->and($tenant->domains->first()->domain)->toBe('testbakery');
});
