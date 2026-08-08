<?php

use App\Filament\Pages\Platform\UpgradePlan;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('upgrade plan page renders for manager', function () {
    Livewire::test(UpgradePlan::class)->assertOk();
});
