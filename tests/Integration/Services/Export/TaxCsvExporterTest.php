<?php

use App\Enums\Financial\ExpenseCategory;
use App\Enums\Financial\IncomeSource;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Financial\TaxCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('writeOrdersCsv includes header row and order data', function () {
    $order = Order::factory()->paid()->create([
        'created_at' => '2025-06-15 10:00:00',
        'total' => 50.00,
    ]);

    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 2,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeOrdersCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== ORDERS ===')
        ->toContain('Date,"Order Number",Customer')
        ->toContain($order->order_number)
        ->toContain('50.00');
});

test('writeOrdersCsv excludes orders outside date range', function () {
    Order::factory()->paid()->create([
        'created_at' => '2024-06-15 10:00:00',
        'total' => 75.00,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeOrdersCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== ORDERS ===')
        ->not->toContain('75.00');
});

test('writeExpensesCsv maps categories to IRS Schedule C lines', function () {
    Expense::factory()->create([
        'date' => '2025-06-15',
        'category' => ExpenseCategory::Ingredients,
        'amount' => 100.00,
        'business_percentage' => 100,
    ]);

    Expense::factory()->create([
        'date' => '2025-07-10',
        'category' => ExpenseCategory::Marketing,
        'amount' => 50.00,
        'business_percentage' => 100,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeExpensesCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== EXPENSES ===')
        ->toContain('Cost of Goods Sold (Line 4)')
        ->toContain('Advertising (Line 8)');
});

test('writeIncomeCsv includes income rows', function () {
    Income::factory()->create([
        'date' => '2025-06-15',
        'source' => IncomeSource::FarmersMarket,
        'amount' => 200.00,
        'description' => 'Saturday market sales',
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeIncomeCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== INCOME ===')
        ->toContain('Date,Source,Description,Amount,Category')
        ->toContain('Saturday market sales')
        ->toContain('Gross Receipts (Schedule C Line 1)');
});

test('writeIncomeCsv excludes income outside date range', function () {
    Income::factory()->create([
        'date' => '2024-03-10',
        'amount' => 300.00,
        'description' => 'Old income',
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeIncomeCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== INCOME ===')
        ->not->toContain('Old income');
});

test('writeSummaryCsv calculates correct totals', function () {
    Order::factory()->paid()->create([
        'created_at' => '2025-06-15 10:00:00',
        'total' => 100.00,
    ]);

    Order::factory()->paid()->create([
        'created_at' => '2025-07-20 10:00:00',
        'total' => 150.00,
    ]);

    Income::factory()->create([
        'date' => '2025-08-01',
        'amount' => 75.00,
    ]);

    Expense::factory()->create([
        'date' => '2025-06-01',
        'amount' => 40.00,
        'business_percentage' => 100,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== TAX SUMMARY ===')
        ->toContain('Total Revenue (Orders)')
        ->toContain('250.00')
        ->toContain('Total Revenue (Other Income)')
        ->toContain('75.00')
        ->toContain('Total Revenue (Combined)')
        ->toContain('325.00');
});

test('writeSummaryCsv includes net profit calculation', function () {
    Order::factory()->paid()->create([
        'created_at' => '2025-06-15 10:00:00',
        'total' => 200.00,
    ]);

    Expense::factory()->create([
        'date' => '2025-06-01',
        'amount' => 80.00,
        'business_percentage' => 50,
    ]);

    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('Net Profit (Revenue - Deductible)');
});
