<?php

use App\Models\Inventory\Product;
use App\Models\Platform\Tenant;
use App\Services\Export\CsvExportService;
use App\Services\Export\TenantArchiveExporter;
use App\Services\Export\TenantExportResponseFactory;
use App\Services\Tenants\TenancyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    Date::setTestNow('2026-09-30 14:05:09');

    $tenancyManager = Mockery::mock(TenancyManager::class);
    $tenancyManager->shouldReceive('withinTenant')
        ->andReturnUsing(fn (Tenant $tenant, callable $callback): mixed => $callback($tenant));

    test()->tenant = Tenant::factory()->make(['id' => 'response-bakery']);
    test()->factory = new TenantExportResponseFactory(
        new CsvExportService,
        new TenantArchiveExporter(new CsvExportService, $tenancyManager),
        $tenancyManager,
    );
});

function streamedContent(StreamedResponse $response): string
{
    ob_start();
    $response->sendContent();

    return (string) ob_get_clean();
}

test('csv responds with a streamed csv download named after the tenant, type and time', function () {
    $response = test()->factory->csv(test()->tenant, 'products');

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('text/csv')
        ->and($response->headers->get('Content-Disposition'))
        ->toBe('attachment; filename=response-bakery_products_2026-09-30_140509.csv');
});

test('csv streams the tenant rows with a header line', function () {
    Product::factory()->create(['name' => 'Seeded Rye']);

    $content = streamedContent(test()->factory->csv(test()->tenant, 'products'));

    expect($content)->toStartWith('ID,Name,Slug,Description,Price,Status')
        ->toContain('Seeded Rye');
});

test('csv streams nothing for an unknown export type', function () {
    $content = streamedContent(test()->factory->csv(test()->tenant, 'nonexistent'));

    expect($content)->toBeEmpty();
});

test('archive responds with a streamed zip download named after the tenant and time', function () {
    $response = test()->factory->archive(test()->tenant);

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('Content-Type'))->toBe('application/zip')
        ->and($response->headers->get('Content-Disposition'))
        ->toBe('attachment; filename=response-bakery_all_data_2026-09-30_140509.zip');
});

test('archive streams a zip containing every export', function () {
    Product::factory()->create(['name' => 'Archived Rye']);

    $content = streamedContent(test()->factory->archive(test()->tenant));
    $path = tempnam(sys_get_temp_dir(), 'response_test_');
    File::put((string) $path, $content);
    $zip = new ZipArchive;
    $zip->open((string) $path);
    $names = array_map(fn (int $index): string => (string) $zip->getNameIndex($index), range(0, $zip->numFiles - 1));
    $products = $zip->getFromName('products.csv');
    $zip->close();
    File::delete((string) $path);

    expect($names)->toBe(['products.csv', 'categories.csv', 'orders.csv', 'customers.csv', 'reviews.csv'])
        ->and($products)->toContain('Archived Rye');
});

test('archive removes its temporary file once streamed', function () {
    $before = File::glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'export_*') ?: [];

    streamedContent(test()->factory->archive(test()->tenant));

    expect(File::glob(sys_get_temp_dir().DIRECTORY_SEPARATOR.'export_*') ?: [])->toBe($before);
});
