<?php

use App\Actions\Tenants\CreateTenant;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Settings\SettingsManager;

beforeEach(function () {
    setUpCentralTest();

    test()->user = User::factory()->owner()->create([
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
    ]);
});

afterEach(function () {
    @unlink(database_path('tenanttestbakery'));
    @unlink(database_path('tenantfailbakery'));
    @unlink(database_path('tenantownsite'));
});

it('creates a tenant with domain and seeds the tenant database', function () {
    $action = resolve(CreateTenant::class);

    $tenant = $action(
        user: testFixture('user', User::class),
        storeName: 'Test Bakery',
        subdomain: 'testbakery',
        useKneadItStorefront: true,
    );

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($tenant->id)->toBe('testbakery')
        ->and($tenant->store_name)->toBe('Test Bakery')
        ->and($tenant->storefront_enabled)->toBeTrue()
        ->and($tenant->is_active)->toBeTrue()
        ->and($tenant->domains)->toHaveCount(1)
        ->and($tenant->domains->first()->domain)->toBe('testbakery');
});

it('persists external website and disables storefront when storefront choice is "own"', function () {
    $action = resolve(CreateTenant::class);

    $tenant = $action(
        user: testFixture('user', User::class),
        storeName: 'Own Site Bakery',
        subdomain: 'ownsite',
        useKneadItStorefront: false,
        externalWebsite: 'https://my-bakery.example.com',
    );

    expect($tenant)
        ->storefront_enabled->toBeFalse()
        ->external_website->toBe('https://my-bakery.example.com');

    $tenant->run(function () {
        test()->assertDatabaseHas('settings', [
            'key' => 'external_website',
            'value' => 'https://my-bakery.example.com',
        ]);
        test()->assertDatabaseHas('settings', [
            'key' => 'storefront_enabled',
            'value' => '0',
        ]);
    });
});

it('rolls back the central tenant + domain row when tenant-DB seeding fails', function () {
    // Force the tenant-DB seed step to throw by replacing the SettingsManager
    // binding with one that explodes on setMany().
    app()->bind(SettingsManager::class, function () {
        return new class {
            public function setMany(array $settings): void
            {
                throw new RuntimeException('Simulated tenant DB seed failure');
            }
        };
    });

    expect(fn () => resolve(CreateTenant::class)(
        user: testFixture('user', User::class),
        storeName: 'Fail Bakery',
        subdomain: 'failbakery',
        useKneadItStorefront: true,
    ))->toThrow(RuntimeException::class, 'Simulated tenant DB seed failure');

    // The action wraps the tenant-DB seed in try/catch and cascade-deletes
    // the central tenant on failure. The caller sees an all-or-nothing
    // outcome: no orphan central row, no leftover SQLite file.
    expect(Tenant::query()->whereKey('failbakery')->exists())->toBeFalse()
        ->and(file_exists(database_path('tenantfailbakery')))->toBeFalse();
});
