<?php

use App\Filament\Pages\Platform\UpgradePlan;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());

    $tenant = new Tenant;
    $tenant->forceFill([
        'id' => 'upgrade-test',
        'plan' => App\Enums\Platform\SubscriptionTier::Starter,
    ]);
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);
});

test('upgrade plan page renders for manager', function () {
    Livewire::test(UpgradePlan::class)->assertOk();
});
