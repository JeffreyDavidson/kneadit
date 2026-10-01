<?php

use App\Filament\Pages\Tools\TaxExport;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
    Feature::define('growth-features', fn () => true);
});

test('tax export page can render', function () {
    livewire(TaxExport::class)
        ->assertOk();
});

test('tax export defaults to the bakery-local year', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2027-01-01 01:00');

    $component = livewire(TaxExport::class);

    $component->assertSet('selectedYear', 2026);
});
