<?php

use App\Actions\Inventory\ImportProducts;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use JMac\Testing\Double;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it imports new products from csv', function () {
    $csv = "name,description,price,category,is_active\n"
        . "Sourdough Loaf,Fresh baked daily,8.50,Breads,1\n"
        . "Chocolate Cake,Rich and decadent,25.00,Cakes,1\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(2)
        ->and($result['updated'])->toBe(0)
        ->and($result['errors'])->toBeEmpty()
        ->and(Product::query()->count())->toBe(2)
        ->and(Category::query()->count())->toBe(2);
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
        ->and($result['updated'])->toBe(1)
        ->and(Product::query()->where('name', 'Sourdough Loaf')->first()->price->dollars())->toBe(9.00);
});

test('it skips rows with row-level errors from parser', function () {
    $exporter = Double::for(App\Services\Export\ProductCsvExporter::class);
    $exporter->expects('parseForPreview')->returns([
        'rows' => [
            [
                'name' => '',
                'description' => 'Missing name product',
                'price' => '5.00',
                'category' => 'Breads',
                'is_active' => '1',
                '_line' => 2,
                '_errors' => ['Name is required'],
            ],
            [
                'name' => 'Valid Product',
                'description' => 'Good product',
                'price' => '10.00',
                'category' => 'Breads',
                'is_active' => '1',
                '_line' => 3,
                '_errors' => [],
            ],
        ],
        'errors' => [],
    ]);

    $action = new ImportProducts($exporter);
    $file = UploadedFile::fake()->createWithContent('products.csv', 'dummy');

    $result = $action($file);

    expect($result['created'])->toBe(1)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0])->toContain('Name is required');
});

test('it includes cost when provided in csv', function () {
    $csv = "name,description,price,cost,category,is_active\n"
        . "Sourdough Loaf,Fresh baked,8.50,3.50,Breads,1\n";

    $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(1)
        ->and($result['errors'])->toBeEmpty();

    $product = Product::query()->where('name', 'Sourdough Loaf')->first();
    expect($product->cost->dollars())->toBe(3.50);
});

test('it catches throwable during product save and records error', function () {
    $exporter = Double::for(App\Services\Export\ProductCsvExporter::class);
    $exporter->expects('parseForPreview')->returns([
        'rows' => [
            [
                'name' => 'Test Product',
                'description' => 'A product',
                'price' => '10.00',
                'category' => '',
                'is_active' => '1',
                'is_featured' => '0',
                '_line' => 2,
                '_errors' => [],
            ],
        ],
        'errors' => [],
    ]);

    Product::creating(function () {
        throw new RuntimeException('Simulated DB error');
    });

    $action = new ImportProducts($exporter);
    $file = UploadedFile::fake()->createWithContent('products.csv', 'dummy');

    $result = $action($file);

    expect($result['errors'])->not->toBeEmpty()
        ->and($result['errors'][0])->toContain('Simulated DB error');
});
