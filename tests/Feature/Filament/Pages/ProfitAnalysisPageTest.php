<?php

use App\Filament\Pages\Analytics\ProfitAnalysis;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('profit analysis page renders for manager', function () {
    Product::factory()->count(3)->create();

    Livewire::test(ProfitAnalysis::class)->assertOk();
});

test('changing the sort order re-renders without error', function () {
    Product::factory()->count(2)->create();

    Livewire::test(ProfitAnalysis::class)
        ->set('sortBy', 'name_asc')
        ->assertOk();
});
