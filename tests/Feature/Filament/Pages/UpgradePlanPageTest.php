<?php

use App\Filament\Pages\Platform\UpgradePlan;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $tenant = new Tenant;
    $tenant->forceFill([
        'id' => 'test-bakery',
        'plan' => App\Enums\Platform\SubscriptionTier::Starter,
    ]);

    app()->instance(TenantContract::class, $tenant);
});

test('upgrade plan page renders for manager', function () {
    Livewire::test(UpgradePlan::class)->assertOk();
});
