<?php

use App\Filament\Pages\Tools\RecipeCostCalculator;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('recipe cost calculator can render and calculate', function () {
    Livewire::test(RecipeCostCalculator::class)
        ->assertOk()
        ->call('refreshAnalysis')
        ->assertOk();
});
