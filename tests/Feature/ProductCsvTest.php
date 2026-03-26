<?php

use App\Actions\Inventory\ImportProducts;
use App\Models\Category;
use App\Models\Product;
use App\Services\Export\ProductCsvExporter;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    config(['database.connections.central' => config('database.connections.sqlite')]);
    $tenantMigrationPath = database_path('migrations/tenant');
    if (is_dir($tenantMigrationPath)) {
        test()->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
    }

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

    expect($headers)->toContain('name');
    expect($headers)->toContain('price');
    expect($headers)->toContain('category');
});

test('export includes all active products', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
    Product::query()->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8, 'category_id' => $category->id, 'is_active' => true]);
    Product::query()->create(['name' => 'Rye', 'slug' => 'rye', 'price' => 7, 'category_id' => $category->id, 'is_active' => true]);

    $csv = $this->service->export();
    expect($csv)->toContain('Sourdough');
    expect($csv)->toContain('Rye');
});

test('import creates new products', function () {
    Category::query()->create(['name' => 'Cakes', 'slug' => 'cakes']);
    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nChocolate Cake,Cakes,Rich and moist,25.00,,1,0\n");

    $result = resolve(ImportProducts::class)($file);

    expect($result['created'])->toBe(1);
    $this->assertDatabaseHas('products', ['name' => 'Chocolate Cake', 'price' => 25.00]);
});

test('import updates existing products by name', function () {
    $category = Category::query()->create(['name' => 'Bread', 'slug' => 'bread']);
    Product::query()->create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8, 'category_id' => $category->id, 'is_active' => true]);

    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nSourdough,Bread,Updated desc,10.00,,1,0\n");
    $result = resolve(ImportProducts::class)($file);

    expect($result['updated'])->toBe(1);
    expect($result['created'])->toBe(0);
    expect((float) Product::query()->where('name', 'Sourdough')->first()->price)->toBe(10.00);
});

test('import handles missing required fields', function () {
    $file = createCsvFile("name,category\nTest,Bread\n");
    $result = resolve(ImportProducts::class)($file);

    expect($result['errors'])->not->toBeEmpty();
    expect($result['created'])->toBe(0);
});

test('import handles invalid prices', function () {
    $file = createCsvFile("name,category,description,price,cost,is_active,is_featured\nBad Item,Bread,desc,notanumber,,1,0\n");
    $result = resolve(ImportProducts::class)($file);

    expect($result['errors'])->not->toBeEmpty();
    expect($result['created'])->toBe(0);
});

test('template has correct headers', function () {
    $template = $this->service->getTemplateContent();
    $headers = str_getcsv(trim($template));

    expect($headers)->toBe(['name', 'category', 'description', 'price', 'cost', 'is_active', 'is_featured']);
});
