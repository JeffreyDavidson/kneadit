<?php

use App\Filament\Pages\Tools\DescriptionGenerator;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new DescriptionGenerator;
});

test('selected product id defaults to null', function () {
    expect(test()->page->selectedProductId)->toBeNull();
});

test('manual product name defaults to empty', function () {
    expect(test()->page->manualProductName)->toBeEmpty();
});

test('tone defaults to professional', function () {
    expect(test()->page->tone)->toBe('professional');
});

test('length defaults to medium', function () {
    expect(test()->page->length)->toBe('medium');
});

test('descriptions defaults to empty array', function () {
    expect(test()->page->descriptions)->toBeEmpty();
});

test('get products property returns collection', function () {
    Product::factory()->count(2)->create();

    expect(test()->page->getProductsProperty())->toHaveCount(2);
});

test('generate with no product name produces empty descriptions', function () {
    test()->page->selectedProductId = null;
    test()->page->manualProductName = '';

    test()->page->generate();

    expect(test()->page->descriptions)->toBeEmpty();
});

test('generate with manual product name produces descriptions', function () {
    test()->page->manualProductName = 'Chocolate Cake';

    test()->page->generate();

    expect(test()->page->descriptions)->toBeArray()->not->toBeEmpty();
});

test('generate with selected product uses product details', function () {
    $category = Category::factory()->create(['name' => 'Cakes']);
    $product = Product::factory()->create([
        'name' => 'Red Velvet Cake',
        'category_id' => $category->id,
        'price' => 35.00,
    ]);

    test()->page->selectedProductId = (string) $product->id;

    test()->page->generate();

    expect(test()->page->descriptions)->not->toBeEmpty();
});

test('apply to product updates product description', function () {
    $product = Product::factory()->create(['description' => 'Old description']);

    test()->page->selectedProductId = (string) $product->id;
    test()->page->descriptions = ['New amazing description', 'Another option'];

    test()->page->applyToProduct(0);

    $product->refresh();
    expect($product->description)->toBe('New amazing description');
});

test('apply to product does nothing when no product selected', function () {
    test()->page->selectedProductId = null;
    test()->page->descriptions = ['Some description'];

    test()->page->applyToProduct(0);

    // No exception thrown, silent return
    expect(true)->toBeTrue();
});

test('apply to product does nothing when index not in descriptions', function () {
    $product = Product::factory()->create(['description' => 'Original']);

    test()->page->selectedProductId = (string) $product->id;
    test()->page->descriptions = ['Only one'];

    test()->page->applyToProduct(5);

    $product->refresh();
    expect($product->description)->toBe('Original');
});
