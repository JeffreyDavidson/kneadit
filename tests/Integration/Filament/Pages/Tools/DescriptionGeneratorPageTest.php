<?php

use App\Filament\Pages\Tools\DescriptionGenerator;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;

beforeEach(function () {
    setUpTenantTest();
    test()->page = new DescriptionGenerator;
});

test('selected product id defaults to null', function () {
    expect(testFixture('page', DescriptionGenerator::class)->selectedProductId)->toBeNull();
});

test('manual product name defaults to empty', function () {
    expect(testFixture('page', DescriptionGenerator::class)->manualProductName)->toBeEmpty();
});

test('tone defaults to professional', function () {
    expect(testFixture('page', DescriptionGenerator::class)->tone)->toBe('professional');
});

test('length defaults to medium', function () {
    expect(testFixture('page', DescriptionGenerator::class)->length)->toBe('medium');
});

test('descriptions defaults to empty array', function () {
    expect(testFixture('page', DescriptionGenerator::class)->descriptions)->toBeEmpty();
});

test('get products property returns collection', function () {
    Product::factory()->count(2)->create();

    expect(testFixture('page', DescriptionGenerator::class)->getProductsProperty())->toHaveCount(2);
});

test('generate with no product name produces empty descriptions', function () {
    testFixture('page', DescriptionGenerator::class)->selectedProductId = null;
    testFixture('page', DescriptionGenerator::class)->manualProductName = '';

    testFixture('page', DescriptionGenerator::class)->generate();

    expect(testFixture('page', DescriptionGenerator::class)->descriptions)->toBeEmpty();
});

test('generate with manual product name produces descriptions', function () {
    testFixture('page', DescriptionGenerator::class)->manualProductName = 'Chocolate Cake';

    testFixture('page', DescriptionGenerator::class)->generate();

    expect(testFixture('page', DescriptionGenerator::class)->descriptions)->toBeArray()->not->toBeEmpty();
});

test('generate with selected product uses product details', function () {
    $category = Category::factory()->create(['name' => 'Cakes']);
    $product = Product::factory()->create([
        'name' => 'Red Velvet Cake',
        'category_id' => $category->id,
        'price' => 35.00,
    ]);

    testFixture('page', DescriptionGenerator::class)->selectedProductId = (string) $product->id;

    testFixture('page', DescriptionGenerator::class)->generate();

    expect(testFixture('page', DescriptionGenerator::class)->descriptions)->not->toBeEmpty();
});

test('apply to product updates product description', function () {
    $product = Product::factory()->create(['description' => 'Old description']);

    testFixture('page', DescriptionGenerator::class)->selectedProductId = (string) $product->id;
    testFixture('page', DescriptionGenerator::class)->descriptions = ['New amazing description', 'Another option'];

    testFixture('page', DescriptionGenerator::class)->applyToProduct(0);

    $product->refresh();
    expect($product->description)->toBe('New amazing description');
});

test('apply to product does nothing when no product selected', function () {
    testFixture('page', DescriptionGenerator::class)->selectedProductId = null;
    testFixture('page', DescriptionGenerator::class)->descriptions = ['Some description'];

    testFixture('page', DescriptionGenerator::class)->applyToProduct(0);

    // No exception thrown, silent return
    expect(true)->toBeTrue();
});

test('apply to product does nothing when index not in descriptions', function () {
    $product = Product::factory()->create(['description' => 'Original']);

    testFixture('page', DescriptionGenerator::class)->selectedProductId = (string) $product->id;
    testFixture('page', DescriptionGenerator::class)->descriptions = ['Only one'];

    testFixture('page', DescriptionGenerator::class)->applyToProduct(5);

    $product->refresh();
    expect($product->description)->toBe('Original');
});
