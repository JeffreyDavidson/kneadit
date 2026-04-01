<?php

use App\Actions\Inventory\ImportProducts;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it imports new products from csv', function () {
    $csv = "name,description,price,category,is_active\n"
        . "Sourdough Loaf,Fresh baked daily,8.50,Breads,1\n"
        . "Chocolate Cake,Rich and decadent,25.00,Cakes,1\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(2)
        ->and($result['updated'])->toBe(0)
        ->and($result['errors'])->toBeEmpty();

    expect(Product::query()->count())->toBe(2);
    expect(Category::query()->count())->toBe(2);
});

test('it updates existing products on reimport', function () {
    $category = Category::factory()->create(['name' => 'Breads']);
    Product::factory()->create([
        'name' => 'Sourdough Loaf',
        'price' => 7.00,
        'category_id' => $category->id,
    ]);

    $csv = "name,description,price,category,is_active\n"
        . "Sourdough Loaf,Updated description,9.00,Breads,1\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(0)
        ->and($result['updated'])->toBe(1);

    expect(Product::query()->where('name', 'Sourdough Loaf')->first()->price)->toBe('9.00');
});
