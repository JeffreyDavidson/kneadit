<?php

use App\Filament\Pages\Analytics\FinanceSummary;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('finance summary page renders with default current-year data', function () {
    $component = livewire(FinanceSummary::class);

    $component->assertOk();
    $component->assertSet('selectedYear', now()->year);
});

test('switching the year reloads financial data', function () {
    $component = livewire(FinanceSummary::class);

    $component->set('selectedYear', now()->year - 1);
    $component->assertOk();
    $component->assertSet('selectedYear', now()->year - 1);
});
