<?php

use App\Models\Orders\Order;
use App\Services\Financial\TaxCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('it writes orders csv with correct headers and data', function () {
    Order::factory()->paid()->create([
        'created_at' => '2025-06-15 10:00:00',
        'total' => 50.00,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeOrdersCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== ORDERS ===')
        ->toContain('Date,"Order Number",Customer');
});

test('it writes summary csv with revenue totals', function () {
    Order::factory()->paid()->create([
        'created_at' => '2025-06-15 10:00:00',
        'total' => 100.00,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== TAX SUMMARY ===')
        ->toContain('Total Revenue (Orders)');
});
