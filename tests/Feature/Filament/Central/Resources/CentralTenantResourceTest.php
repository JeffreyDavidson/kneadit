<?php

use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->platformAdmin()->create());
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

test('tenant table supports listing columns searching and filters', function () {
    createTestTenant('sweet-bakes', [
        'store_name' => 'Sweet Bakes',
        'email' => 'sweet@test.com',
        'plan' => 'starter',
        'is_active' => true,
    ]);
    Livewire::test(ListTenants::class)
        ->assertOk();

    foreach (['id', 'store_name', 'name', 'email', 'plan', 'is_active'] as $column) {
        Livewire::test(ListTenants::class)
            ->assertCanRenderTableColumn($column);
    }

    createTestTenant('rustic-loaf', [
        'store_name' => 'Rustic Loaf',
        'email' => 'rustic@test.com',
        'plan' => 'pro',
        'is_active' => false,
    ]);

    Livewire::test(ListTenants::class)
        ->searchTable('Sweet')
        ->assertOk();
    Livewire::test(ListTenants::class)
        ->filterTable('plan', 'starter')
        ->assertOk();
    Livewire::test(ListTenants::class)
        ->filterTable('is_active', true)
        ->assertOk();
});
