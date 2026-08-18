<?php

use App\Filament\Pages\Analytics\FinanceSummary;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('finance summary page renders with default current-year data', function () {
    Livewire::test(FinanceSummary::class)
        ->assertOk()
        ->assertSet('selectedYear', now()->year);
});

test('switching the year reloads financial data', function () {
    Livewire::test(FinanceSummary::class)
        ->set('selectedYear', now()->year - 1)
        ->assertOk()
        ->assertSet('selectedYear', now()->year - 1);
});
