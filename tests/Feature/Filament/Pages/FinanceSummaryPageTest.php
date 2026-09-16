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

test('profit colors distinguish losses from nonnegative results', function (float $net, string $color) {
    $component = livewire(FinanceSummary::class);

    $component->set('netProfit', $net);
    $component->set('monthlyBreakdown', collect([
        ['month_name' => 'January', 'revenue' => 100.0, 'expenses' => 100.0 - $net, 'net' => $net],
    ]));

    $component->assertSee("text-3xl font-bold text-{$color}-900", false);
    $component->assertSee("px-4 py-3 text-right font-medium text-{$color}-600", false);
})->with([
    'profit' => [25.0, 'green'],
    'break even' => [0.0, 'green'],
    'loss' => [-25.0, 'red'],
]);
