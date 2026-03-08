<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\ProductCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductCsvTest extends TestCase
{
    use RefreshDatabase;

    protected ProductCsvService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }

        $this->service = new ProductCsvService;
    }

    /** @test */
    public function export_generates_valid_csv_with_headers(): void
    {
        $csv = $this->service->export();
        $lines = explode("\n", trim($csv));
        $headers = str_getcsv($lines[0]);

        $this->assertContains('name', $headers);
        $this->assertContains('price', $headers);
        $this->assertContains('category', $headers);
    }

    /** @test */
    public function export_includes_all_active_products(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8, 'category_id' => $category->id, 'is_active' => true]);
        Product::create(['name' => 'Rye', 'slug' => 'rye', 'price' => 7, 'category_id' => $category->id, 'is_active' => true]);

        $csv = $this->service->export();
        $this->assertStringContainsString('Sourdough', $csv);
        $this->assertStringContainsString('Rye', $csv);
    }

    /** @test */
    public function import_creates_new_products(): void
    {
        Category::create(['name' => 'Cakes', 'slug' => 'cakes']);
        $file = $this->createCsvFile("name,category,description,price,cost,is_active,is_featured\nChocolate Cake,Cakes,Rich and moist,25.00,,1,0\n");

        $result = $this->service->import($file);

        $this->assertEquals(1, $result['created']);
        $this->assertDatabaseHas('products', ['name' => 'Chocolate Cake', 'price' => 25.00]);
    }

    /** @test */
    public function import_updates_existing_products_by_name(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 8, 'category_id' => $category->id, 'is_active' => true]);

        $file = $this->createCsvFile("name,category,description,price,cost,is_active,is_featured\nSourdough,Bread,Updated desc,10.00,,1,0\n");
        $result = $this->service->import($file);

        $this->assertEquals(1, $result['updated']);
        $this->assertEquals(0, $result['created']);
        $this->assertEquals(10.00, (float) Product::where('name', 'Sourdough')->first()->price);
    }

    /** @test */
    public function import_handles_missing_required_fields(): void
    {
        $file = $this->createCsvFile("name,category\nTest,Bread\n");
        $result = $this->service->import($file);

        $this->assertNotEmpty($result['errors']);
        $this->assertEquals(0, $result['created']);
    }

    /** @test */
    public function import_handles_invalid_prices(): void
    {
        $file = $this->createCsvFile("name,category,description,price,cost,is_active,is_featured\nBad Item,Bread,desc,notanumber,,1,0\n");
        $result = $this->service->import($file);

        $this->assertNotEmpty($result['errors']);
        $this->assertEquals(0, $result['created']);
    }

    /** @test */
    public function template_has_correct_headers(): void
    {
        $template = $this->service->getTemplateContent();
        $headers = str_getcsv(trim($template));

        $this->assertEquals(['name', 'category', 'description', 'price', 'cost', 'is_active', 'is_featured'], $headers);
    }

    private function createCsvFile(string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($path, $content);

        return new UploadedFile($path, 'products.csv', 'text/csv', null, true);
    }
}
