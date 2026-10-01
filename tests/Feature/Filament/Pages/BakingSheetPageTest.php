<?php

use App\Filament\Pages\Operations\BakingSheet;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('baking sheet loads for selected date', function () {
    $component = livewire(BakingSheet::class);

    $component->assertOk();
    $component->set('selectedDate', now()->format('Y-m-d'));
    $component->assertOk();
});

test('baking sheet opens on the bakery-local date in the evening', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    livewire(BakingSheet::class)
        ->assertSet('selectedDate', '2026-10-05');
});
