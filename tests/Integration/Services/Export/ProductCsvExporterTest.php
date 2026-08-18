<?php

use App\Models\Inventory\Product;
use App\Services\Export\ProductCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('template content has csv headers', function () {
    $exporter = new ProductCsvExporter;
    $template = $exporter->getTemplateContent();

    expect($template)->toContain('name')
        ->toContain('price')
        ->toContain('category');
});

test('exports products as csv', function () {
    Product::factory()->count(3)->create();

    $exporter = new ProductCsvExporter;
    $csv = $exporter->export();

    $lines = explode('
', trim($csv));

    expect($lines)->toHaveCount(4); // header + 3 products
});

test('parses valid csv for preview', function () {
    $csvContent = "name,price,category\nChocolate Cake,12.99,Cakes\nSourdough,8.50,Bread";
    $file = Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csvContent);

    $exporter = new ProductCsvExporter;
    $result = $exporter->parseForPreview($file);

    expect($result['rows'])->toHaveCount(2)
        ->and($result['errors'])->toBeEmpty()
        ->and($result['rows'][0]['name'])->toBe('Chocolate Cake');
});

test('reports missing required columns in csv', function () {
    $csvContent = "description,category\nA cake,Cakes";
    $file = Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csvContent);

    $exporter = new ProductCsvExporter;
    $result = $exporter->parseForPreview($file);

    expect($result['rows'])->toBeEmpty()
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0])->toContain('Missing required columns');
});
