<?php

use App\Filament\Pages\Settings\CustomDomain;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $fakeTenant = new Tenant;
    $fakeTenant->forceFill([
        'id' => 'test-bakery',
        'plan' => 'pro',
        'custom_domain' => null,
    ]);

    app()->instance(TenantContract::class, $fakeTenant);
});

test('custom domain page can render', function () {
    livewire(CustomDomain::class)
        ->assertOk();
});
