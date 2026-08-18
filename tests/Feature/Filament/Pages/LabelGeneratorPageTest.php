<?php

use App\Filament\Pages\Tools\LabelGenerator;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('label generator shows preview after selecting products', function () {
    $product = Product::factory()->create();

    Livewire::test(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSet('showPreview', true);
});
