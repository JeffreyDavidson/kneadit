<?php

use App\Filament\Pages\Tools\PricingEngine;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('pricing engine calculates with products', function () {
    Product::factory()->count(3)->create(['price' => 10.00, 'cost' => 4.00]);

    Livewire::test(PricingEngine::class)
        ->call('calculate')
        ->assertOk();
});
