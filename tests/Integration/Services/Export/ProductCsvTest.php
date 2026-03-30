<?php

use App\Actions\Inventory\ImportProducts;
use App\Models\Category;
use App\Models\Product;
use App\Services\Export\ProductCsvExporter;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    setUpTenantTest();

    $this->service = new ProductCsvExporter;
});

function createCsvFile(string $content): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'csv_');
    file_put_contents($path, $content);

    return new UploadedFile($path, 'products.csv', 'text/csv', null, true);
}

test('export generates valid csv with headers', function () {
    $csv = $this->service->export();
    $lines = explode("\n", trim($csv));
    $headers = str_getcsv($lines[0]);

    expect($headers)->toContain('name')->toContain('price')->toContain('category');
});

test('export includes all active products', function () {
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
    Product::factory()->for($category)->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8]);
    Product::factory()->for($category)->create(['name' => 'Rye', 'slug' => 'rye', 'price' => 7]);

    $csv = $this->service->export();
    expect($csv)->toContain('Sourdough')->toContain('Rye');
});

test('import creates new products', function () {
    Category::factory()->create(['name' => 'Cakes', 'slug' => 'cakes']);
    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nChocolate Cake,Cakes,Rich and moist,25.00,,1,0\n");

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(1);
    $this->assertDatabaseHas('products', ['name' => 'Chocolate Cake', 'price' => 25.00]);
});

test('import updates existing products by name', function () {
    $category = Category::factory()->create(['name' => 'Bread', 'slug' => 'bread']);
    Product::factory()->for($category)->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8]);

    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nSourdough,Bread,Updated desc,10.00,,1,0\n");
    $result = resolve(ImportProducts::class)($file);
    expect($result)->toMatchArray(['updated' => 1, 'created' => 0])->and((float) Product::query()->where('name', 'Sourdough')->first()->price)->toBe(10.00);
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
    $template = $this->service->getTemplateContent();
    $headers = str_getcsv(trim($template));

    expect($headers)->toBe(['name', 'category', 'description', 'price', 'cost', 'is_active', 'is_featured']);
});
