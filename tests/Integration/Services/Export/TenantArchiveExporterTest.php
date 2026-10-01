<?php

use App\Models\Inventory\Product;
use App\Models\Platform\Tenant;
use App\Services\Export\CsvExportService;
use App\Services\Export\TenantArchiveExporter;
use App\Services\Tenants\TenancyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    test()->tenant = Tenant::factory()->make(['id' => 'archive-bakery']);
});

function archiveTenancyManagerPassingThrough(): TenancyManager
{
    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->once()
        ->andReturnUsing(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    return $tenancyManager;
}

/** @return list<string> */
function exportTemporaryFiles(): array
{
    return File::glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'export_*') ?: [];
}

test('create builds a zip with one csv per export type', function () {
    $exporter = new TenantArchiveExporter(new CsvExportService, archiveTenancyManagerPassingThrough());

    $path = $exporter->create(test()->tenant);
    $zip = new ZipArchive;
    $zip->open($path);
    $entries = array_map(fn (int $index): string => (string) $zip->getNameIndex($index), range(0, $zip->numFiles - 1));
    $zip->close();
    File::delete($path);

    expect($entries)->toBe(['products.csv', 'categories.csv', 'orders.csv', 'customers.csv', 'reviews.csv']);
});

test('create fills the csv entries with the tenant data', function () {
    Product::factory()->create(['name' => 'Rye Loaf']);
    $exporter = new TenantArchiveExporter(new CsvExportService, archiveTenancyManagerPassingThrough());

    $path = $exporter->create(test()->tenant);
    $zip = new ZipArchive;
    $zip->open($path);
    $products = $zip->getFromName('products.csv');
    $customers = $zip->getFromName('customers.csv');
    $zip->close();
    File::delete($path);

    expect($products)->toContain('ID,Name,Slug,Description,Price,Status')
        ->toContain('Rye Loaf')
        ->and($customers)->toContain('ID,Name,Email,"Created At","Updated At"');
});

test('create reads the data inside the tenant context', function () {
    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->once()
        ->andReturnUsing(function (Tenant $tenant, callable $callback): mixed {
            expect($tenant->id)->toBe('archive-bakery');

            return $callback($tenant);
        });
    $exporter = new TenantArchiveExporter(new CsvExportService, $tenancyManager);

    $path = $exporter->create(test()->tenant);
    File::delete($path);

    expect($path)->not->toBeEmpty();
});

test('create removes the temporary file and rethrows when the export fails', function () {
    $before = exportTemporaryFiles();
    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')->once()->andThrow(new RuntimeException('Tenant database unavailable'));
    $exporter = new TenantArchiveExporter(new CsvExportService, $tenancyManager);

    $attempt = fn (): string => $exporter->create(test()->tenant);

    expect($attempt)->toThrow(RuntimeException::class, 'Tenant database unavailable')
        ->and(exportTemporaryFiles())->toBe($before);
});
