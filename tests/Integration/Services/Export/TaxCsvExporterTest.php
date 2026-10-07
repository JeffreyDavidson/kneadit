<?php

use App\Enums\Financial\ExpenseCategory;
use App\Enums\Financial\IncomeSource;
use App\Enums\Orders\PaymentStatus;
use App\Models\Financial\Expense;
use App\Models\Financial\Income;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Financial\TaxCsvExporter;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

/** @return resource */
function taxCsvMemoryStream()
{
    $handle = fopen('php://memory', 'r+');

    if ($handle === false) {
        throw new RuntimeException('Unable to open an in-memory CSV stream.');
    }

    return $handle;
}

test('writeOrdersCsv includes header row and order data', function () {
    $order = Order::factory()->paid()->create([
        'delivery_date' => '2025-06-15',
        'total' => 50.00,
    ]);

    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'quantity' => 2,
    ]);

    $handle = taxCsvMemoryStream();
    resolve(TaxCsvExporter::class)->writeOrdersCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('=== ORDERS ===')
        ->toContain('"Delivery Date","Order Number",Customer')
        ->toContain('2025-06-15')
        ->toContain($order->order_number)
        ->toContain('50.00');
});

test('writeOrdersCsv excludes orders outside date range', function () {
    Order::factory()->paid()->create([
        'created_at' => '2025-06-15 10:00:00',
        'delivery_date' => '2024-06-15',
        'total' => 75.00,
    ]);

    $handle = taxCsvMemoryStream();
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

    $handle = taxCsvMemoryStream();
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

    $handle = taxCsvMemoryStream();
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

    $handle = taxCsvMemoryStream();
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
        'delivery_date' => '2025-06-15',
        'total' => 100.00,
    ]);

    Order::factory()->paid()->create([
        'delivery_date' => '2025-07-20',
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

    $handle = taxCsvMemoryStream();
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
        'delivery_date' => '2025-06-15',
        'total' => 200.00,
    ]);

    Expense::factory()->create([
        'date' => '2025-06-01',
        'amount' => 80.00,
        'business_percentage' => 50,
    ]);

    $handle = taxCsvMemoryStream();
    resolve(TaxCsvExporter::class)->writeSummaryCsv($handle, '2025-01-01', '2025-12-31');

    rewind($handle);
    $output = stream_get_contents($handle);
    fclose($handle);

    expect($output)
        ->toContain('Net Profit (Revenue - Deductible)');
});

test('the orders list and summary count only revenue orders: paid and not cancelled', function () {
    Order::factory()->paid()->create(['delivery_date' => '2025-06-15', 'total' => 11.00]);
    Order::factory()->paid()->cancelled()->create(['delivery_date' => '2025-06-15', 'total' => 22.00]);
    Order::factory()->unpaid()->create(['delivery_date' => '2025-06-15', 'total' => 33.00]);
    Order::factory()->paid()->create(['delivery_date' => '2025-06-15', 'payment_status' => PaymentStatus::Refunded, 'total' => 44.00]);

    $orders = taxCsvMemoryStream();
    $summary = taxCsvMemoryStream();
    resolve(TaxCsvExporter::class)->writeOrdersCsv($orders, '2025-01-01', '2025-12-31');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($summary, '2025-01-01', '2025-12-31');
    rewind($orders);
    rewind($summary);
    $ordersOutput = stream_get_contents($orders);
    $summaryOutput = stream_get_contents($summary);

    expect($ordersOutput)->toContain('11.00')->not->toContain('22.00')->not->toContain('33.00')->not->toContain('44.00')
        ->and($summaryOutput)->toContain('"Total Revenue (Orders)",11.00');
});

test('a Los Angeles bakery order delivered on Dec 31 local time lands in that year', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/Los_Angeles'])));
    // 2026-12-31 19:30 in Los Angeles is already 2027-01-01 03:30 UTC.
    $decemberOrder = Order::factory()->paid()->create(['created_at' => '2027-01-01 03:30:00', 'delivery_date' => '2026-12-31', 'total' => 61.00]);
    $januaryOrder = Order::factory()->paid()->create(['created_at' => '2026-12-30 20:00:00', 'delivery_date' => '2027-01-01', 'total' => 62.00]);

    $orders = taxCsvMemoryStream();
    $summary = taxCsvMemoryStream();
    resolve(TaxCsvExporter::class)->writeOrdersCsv($orders, '2026-01-01', '2026-12-31');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($summary, '2026-01-01', '2026-12-31');
    rewind($orders);
    rewind($summary);
    $ordersOutput = stream_get_contents($orders);
    $summaryOutput = stream_get_contents($summary);

    expect($ordersOutput)->toContain($decemberOrder->order_number)->toContain('2026-12-31')
        ->not->toContain($januaryOrder->order_number)
        ->and($summaryOutput)->toContain('"Total Revenue (Orders)",61.00');
});

test('expenses and income dated on the last day of the range are exported and summarized', function () {
    Expense::factory()->create(['date' => '2025-12-31', 'description' => 'New Year prep flour', 'amount' => 40.00, 'business_percentage' => 100]);
    Income::factory()->create(['date' => '2025-12-31', 'description' => 'Last day market', 'amount' => 25.00]);

    $expenses = taxCsvMemoryStream();
    $income = taxCsvMemoryStream();
    $summary = taxCsvMemoryStream();
    resolve(TaxCsvExporter::class)->writeExpensesCsv($expenses, '2025-01-01', '2025-12-31');
    resolve(TaxCsvExporter::class)->writeIncomeCsv($income, '2025-01-01', '2025-12-31');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($summary, '2025-01-01', '2025-12-31');
    rewind($expenses);
    rewind($income);
    rewind($summary);

    expect(stream_get_contents($expenses))->toContain('New Year prep flour')
        ->and(stream_get_contents($income))->toContain('Last day market')
        ->and(stream_get_contents($summary))
        ->toContain('"Total Revenue (Other Income)",25.00')
        ->toContain('"Total Expenses",40.00')
        ->toContain('"Total Deductible",40.00');
});

test('expenses and income dated the day after the range are left out', function () {
    Expense::factory()->create(['date' => '2026-01-01', 'description' => 'Next year flour', 'amount' => 40.00]);
    Income::factory()->create(['date' => '2026-01-01', 'description' => 'Next year market', 'amount' => 25.00]);

    $expenses = taxCsvMemoryStream();
    $income = taxCsvMemoryStream();
    resolve(TaxCsvExporter::class)->writeExpensesCsv($expenses, '2025-01-01', '2025-12-31');
    resolve(TaxCsvExporter::class)->writeIncomeCsv($income, '2025-01-01', '2025-12-31');
    rewind($expenses);
    rewind($income);

    expect(stream_get_contents($expenses))->not->toContain('Next year flour')
        ->and(stream_get_contents($income))->not->toContain('Next year market');
});
