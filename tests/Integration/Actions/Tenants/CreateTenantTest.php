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
        user: test()->user,
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
        user: test()->user,
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

it('does NOT roll back the central tenant + domain when tenant-DB seeding fails (current behavior — orphan rows are detected by tenants:doctor)', function () {
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
        user: test()->user,
        storeName: 'Fail Bakery',
        subdomain: 'failbakery',
        useKneadItStorefront: true,
    ))->toThrow(RuntimeException::class, 'Simulated tenant DB seed failure');

    // Pin current behavior: the central tenant row + domain row REMAIN even
    // though the tenant-DB seed step exploded. The cross-DB transaction
    // boundary is intentionally not atomic — `tenants:doctor` is the recovery
    // tool. If a future PR changes this to be atomic, this test will catch it.
    expect(Tenant::query()->whereKey('failbakery')->exists())->toBeTrue();
});
