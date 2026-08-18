<?php

use App\Filament\Pages\Tools\PrintableMenu;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Models\Domain;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $fakeTenant = new Tenant;
    $fakeTenant->forceFill([
        'id' => 'test-bakery',
        'custom_domain' => null,
        'plan' => 'pro',
        'store_name' => 'Test Bakery',
    ]);
    $fakeTenant->setRelation('domains', new Collection([
        new Domain(['domain' => 'test-bakery.getkneadit.test']),
    ]));

    app()->instance(TenantContract::class, $fakeTenant);
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('printable menu page can render', function () {
    livewire(PrintableMenu::class)
        ->assertOk();
});
