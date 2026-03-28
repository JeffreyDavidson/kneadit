<?php

use App\Filament\Pages\LabelGenerator;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
});

test('label generator shows preview after selecting products', function () {
    $product = Product::factory()->create();

    Livewire::test(LabelGenerator::class)
        ->set('selectedProducts', [$product->id])
        ->call('generateLabels')
        ->assertSet('showPreview', true);
});
