<?php

use App\Actions\Inventory\ImportProducts;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Services\Export\ProductCsvExporter;
use Illuminate\Http\UploadedFile;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    setUpTenantTest();

    test()->service = new ProductCsvExporter;
});

function createCsvFile(string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'csv_');
    file_put_contents($path, $content);

    return new UploadedFile($path, 'products.csv', 'text/csv', null, true);
}

test('export generates valid csv with headers', function () {
    $csv = test()->service->export();
    $lines = explode("\n", trim($csv));
    $headers = str_getcsv($lines[0]);

    expect($headers)->toContain('name')->toContain('price')->toContain('category');
});

test('export includes all active products', function () {
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
    Product::factory()->for($category)->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8]);
    Product::factory()->for($category)->create(['name' => 'Rye', 'slug' => 'rye', 'price' => 7]);

    $csv = test()->service->export();
    expect($csv)->toContain('Sourdough')->toContain('Rye');
});

test('import creates new products', function () {
    Category::factory()->create(['name' => 'Cakes', 'slug' => 'cakes']);
    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nChocolate Cake,Cakes,Rich and moist,25.00,,1,0\n");

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(1);
    // products.price is bigint cents (migration 2026_04_22_215000).
    assertDatabaseHas('products', ['name' => 'Chocolate Cake', 'price' => 2500]);
});

test('import updates existing products by name', function () {
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
    Product::factory()->for($category)->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8]);

    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nSourdough,Bread,Updated desc,10.00,,1,0\n");
    $result = resolve(ImportProducts::class)($file);
    $price = Product::query()->where('name', 'Sourdough')->firstOrFail()->price;

    if ($price === null) {
        throw new RuntimeException('Expected the imported product to have a price.');
    }

    expect($result)->toMatchArray(['updated' => 1, 'created' => 0])
        ->and($price->dollars())->toBe(10.00);
});

test('import handles missing required fields', function () {
    $file = createCsvFile("name,category\nTest,Bread\n");
    $result = resolve(ImportProducts::class)($file);

    expect($result['errors'])->not->toBeEmpty()->and($result['created'])->toBe(0);
});

test('import handles invalid prices', function () {
    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nBad Item,Bread,desc,notanumber,,1,0\n");
    $result = resolve(ImportProducts::class)($file);

    expect($result['errors'])->not->toBeEmpty()->and($result['created'])->toBe(0);
});

test('template has correct headers', function () {
    $template = test()->service->getTemplateContent();
    $headers = str_getcsv(trim($template));

    expect($headers)->toBe(['name', 'category', 'description', 'price', 'cost', 'is_active', 'is_featured']);
});
