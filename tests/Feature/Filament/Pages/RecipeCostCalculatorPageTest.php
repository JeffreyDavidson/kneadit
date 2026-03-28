<?php

use App\Filament\Pages\RecipeCostCalculator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('recipe cost calculator can render and calculate', function () {
    Livewire::test(RecipeCostCalculator::class)
        ->assertOk()
        ->call('calculateCosts')
        ->assertOk();
});
