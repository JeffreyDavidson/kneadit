<?php

use App\Contracts\Tenants\LegacyCatalogImporter;
use Illuminate\Support\Facades\DB;

beforeEach(fn () => setUpTenantTest());

it('imports categories and products and returns their current ids', function () {
    $result = resolve(LegacyCatalogImporter::class)->import(
        categories: [
            ['id' => 10, 'name' => 'Breads', 'description' => 'Fresh bread'],
        ],
        products: [
            ['id' => 20, 'category_id' => 10, 'name' => 'Sourdough', 'price' => '12.50', 'cost' => '3.25'],
        ],
    );

    $categoryId = (int) DB::table('categories')->where('slug', 'breads')->value('id');
    $productId = (int) DB::table('products')->where('slug', 'sourdough')->value('id');

    expect($result)
        ->toBe([
            'category_ids' => [10 => $categoryId],
            'product_ids' => [20 => $productId],
        ]);

    test()->assertDatabaseHas('categories', [
        'id' => $categoryId,
        'name' => 'Breads',
        'slug' => 'breads',
    ])->assertDatabaseHas('products', [
        'id' => $productId,
        'category_id' => $categoryId,
        'price' => 1250,
        'cost' => 325,
    ]);
});

it('updates existing catalog records without duplicating them', function () {
    $importer = resolve(LegacyCatalogImporter::class);
    $categories = [['id' => 10, 'name' => 'Breads']];
    $products = [['id' => 20, 'category_id' => 10, 'name' => 'Sourdough', 'price' => '12.50']];

    $first = $importer->import($categories, $products);
    $second = $importer->import($categories, $products);

    expect($second)->toEqual($first);
    test()->assertDatabaseCount('categories', 1)->assertDatabaseCount('products', 1);
});
