<?php

use App\Filament\Central\Resources\TenantResource;
use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    $this->actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

function createTestTenant(string $id = 'test-bakery', array $overrides = []): object
{
    $defaults = [
        'id' => $id,
        'name' => 'Test Baker',
        'email' => 'baker@test.com',
        'plan' => 'pro',
        'store_name' => 'Test Bakery',
        'is_active' => true,
        'storefront_enabled' => true,
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    $data = array_merge($defaults, $overrides);

    if (! DB::table('tenants')->where('id', $id)->exists()) {
        DB::table('tenants')->insert($data);
    }

    return DB::table('tenants')->where('id', $id)->first();
}

test('can list tenants in the table', function () {
    createTestTenant('bakery-alpha', ['store_name' => 'Alpha Bakery']);
    createTestTenant('bakery-beta', ['store_name' => 'Beta Bakery', 'email' => 'beta@test.com']);

    Livewire::test(ListTenants::class)
        ->assertOk();
});

test('can render tenant table columns', function (string $column) {
    createTestTenant();

    Livewire::test(ListTenants::class)
        ->assertCanRenderTableColumn($column);
})->with(['id', 'store_name', 'name', 'email', 'plan', 'is_active']);

test('can search tenants by store name', function () {
    createTestTenant('sweet-bakes', ['store_name' => 'Sweet Bakes', 'email' => 'sweet@test.com']);
    createTestTenant('rustic-loaf', ['store_name' => 'Rustic Loaf', 'email' => 'rustic@test.com']);

    Livewire::test(ListTenants::class)
        ->searchTable('Sweet')
        ->assertOk();
});

test('can filter tenants by plan', function () {
    createTestTenant('starter-bakery', ['plan' => 'starter', 'email' => 'starter@test.com']);
    createTestTenant('pro-bakery', ['plan' => 'pro', 'email' => 'pro@test.com']);

    Livewire::test(ListTenants::class)
        ->filterTable('plan', 'starter')
        ->assertOk();
});

test('can filter tenants by active status', function () {
    createTestTenant('active-bakery', ['is_active' => true, 'email' => 'active@test.com']);
    createTestTenant('inactive-bakery', ['is_active' => false, 'email' => 'inactive@test.com']);

    Livewire::test(ListTenants::class)
        ->filterTable('is_active', true)
        ->assertOk();
});

test('resource returns globally searchable attributes', function () {
    expect(TenantResource::getGloballySearchableAttributes())
        ->toBe(['store_name', 'name', 'email', 'id']);
});

test('resource returns global search result details', function () {
    createTestTenant('detail-bakery', [
        'name' => 'Jane Baker',
        'plan' => 'growth',
        'email' => 'detail@test.com',
    ]);

    $tenant = App\Models\Platform\Tenant::find('detail-bakery');
    $details = TenantResource::getGlobalSearchResultDetails($tenant);

    expect($details)
        ->toHaveKey('Owner', 'Jane Baker')
        ->toHaveKey('Plan', 'Growth');
});
