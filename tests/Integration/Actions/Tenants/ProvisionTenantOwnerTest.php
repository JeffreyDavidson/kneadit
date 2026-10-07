<?php

use App\Actions\Tenants\CreateTenantRecord;
use App\Actions\Tenants\ProvisionTenantOwner;
use App\Enums\Staff\UserRole;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    setUpCentralTest();

    test()->user = User::factory()->owner()->create([
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
        'password' => bcrypt('password'),
    ]);
});

afterEach(function () {
    @unlink(testTenantDatabaseFile('provisionbakery'));
    @unlink(testTenantDatabaseFile('provisionownsite'));
});

it('copies the owner into the tenant database keeping the central password hash and verifying the email', function () {
    $tenant = resolve(CreateTenantRecord::class)(test()->user, 'Provision Bakery', 'provisionbakery', true, null);

    resolve(ProvisionTenantOwner::class)($tenant, test()->user, 'Provision Bakery', true, null);

    $owner = $tenant->run(fn () => DB::table('users')->where('email', 'baker@test.com')->first());

    expect($owner)->not->toBeNull()
        ->and($owner->name)->toBe('Test Baker')
        ->and($owner->password)->toBe(test()->user->password)
        ->and($owner->email_verified_at)->not->toBeNull();
});

it('makes the copied user the bakery Owner', function () {
    $tenant = resolve(CreateTenantRecord::class)(test()->user, 'Provision Bakery', 'provisionbakery', true, null);

    resolve(ProvisionTenantOwner::class)($tenant, test()->user, 'Provision Bakery', true, null);

    $role = $tenant->run(fn () => DB::table('users')->where('email', 'baker@test.com')->value('role'));

    expect($role)->toBe(UserRole::Owner->value);
});

it('seeds the store settings and only stores the external website for own-website tenants', function (bool $useKneadItStorefront, string $subdomain, ?string $website, array $expected, array $missing) {
    $tenant = resolve(CreateTenantRecord::class)(test()->user, 'Provision Bakery', $subdomain, $useKneadItStorefront, $website);

    resolve(ProvisionTenantOwner::class)($tenant, test()->user, 'Provision Bakery', $useKneadItStorefront, $website);

    $tenant->run(function () use ($expected, $missing) {
        foreach ($expected as $key => $value) {
            test()->assertDatabaseHas('settings', ['key' => $key, 'value' => $value]);
        }

        foreach ($missing as $key) {
            test()->assertDatabaseMissing('settings', ['key' => $key]);
        }
    });
})->with([
    'kneadit storefront' => [
        true,
        'provisionbakery',
        'https://ignored.example.com',
        ['store_name' => 'Provision Bakery', 'store_email' => 'baker@test.com', 'storefront_enabled' => '1'],
        ['external_website'],
    ],
    'own website' => [
        false,
        'provisionownsite',
        'https://my-bakery.example.com',
        ['store_name' => 'Provision Bakery', 'store_email' => 'baker@test.com', 'storefront_enabled' => '0', 'external_website' => 'https://my-bakery.example.com'],
        [],
    ],
]);
