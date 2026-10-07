<?php

use App\Filament\Central\Resources\TenantResource\Pages\ListTenants;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

use function Pest\Livewire\livewire;

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
    ]);
    livewire(ListTenants::class)
        ->assertOk();

    foreach (['id', 'store_name', 'name', 'plan', 'is_paused', 'trial_ends_at'] as $column) {
        livewire(ListTenants::class)
            ->assertCanRenderTableColumn($column);
    }

    createTestTenant('rustic-loaf', [
        'store_name' => 'Rustic Loaf',
        'email' => 'rustic@test.com',
        'plan' => 'pro',
        'paused_at' => now(),
    ]);

    livewire(ListTenants::class)
        ->searchTable('Sweet')
        ->assertOk();
    livewire(ListTenants::class)
        ->filterTable('plan', 'starter')
        ->assertOk();
    livewire(ListTenants::class)
        ->filterTable('paused', true)
        ->assertOk();
});

test('tenant table keeps the less important columns hidden until toggled so it fits a 1440px screen', function (string $column) {
    createTestTenant('sweet-bakes');

    $table = livewire(ListTenants::class)->instance()->getTable();

    expect($table->getColumn($column)->isToggledHiddenByDefault())->toBeTrue();
})->with(['email', 'storefront_enabled']);

test('tenant table displays the configured storefront host', function () {
    Config::set('app.url', 'https://kneadit.test');
    createTestTenant('sweet-bakes');

    livewire(ListTenants::class)
        ->assertSee('sweet-bakes.kneadit.test');
});

test('platform admins can pause and resume bakeries in bulk', function () {
    createTestTenant('sweet-bakes');
    createTestTenant('rustic-loaf');
    $records = ['sweet-bakes', 'rustic-loaf'];

    livewire(ListTenants::class)
        ->selectTableRecords($records)
        ->callAction(TestAction::make('pause')->table()->bulk());

    expect(DB::table('tenants')->whereIn('id', $records)->whereNotNull('paused_at')->count())->toBe(2)
        ->and(DB::table('tenants')->whereIn('id', $records)->where('storefront_enabled', true)->count())->toBe(2);

    livewire(ListTenants::class)
        ->selectTableRecords($records)
        ->callAction(TestAction::make('resume')->table()->bulk());

    expect(DB::table('tenants')->whereIn('id', $records)->whereNull('paused_at')->count())->toBe(2);
});
