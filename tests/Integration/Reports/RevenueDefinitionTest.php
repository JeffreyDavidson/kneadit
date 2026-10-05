<?php

use App\Enums\Orders\PaymentStatus;
use App\Filament\Widgets\GoalTrackerWidget;
use App\Models\Orders\Order;
use App\Queries\Financial\RevenueQuery;
use App\Reports\Orders\SalesReport;
use App\Services\Financial\FinancialCalculator;
use App\Services\Financial\TaxCsvExporter;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\ValueObjects\DateRange;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    Date::setTestNow('2026-09-16 12:00');

    // Counts as revenue: paid and not cancelled, dated by delivery date.
    // Monday 2026-09-07 to Sunday 2026-09-13 is "last week" for the digest.
    Order::factory()->paid()->confirmed()->create(['total' => 100, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-01 12:00:00']);
    Order::factory()->delivered()->create(['total' => 50, 'delivery_date' => '2026-09-11', 'created_at' => '2026-09-02 12:00:00']);

    // Never revenue: refunded, paid then cancelled, unpaid and part-paid.
    Order::factory()->delivered()->create(['total' => 70, 'payment_status' => PaymentStatus::Refunded, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-03 12:00:00']);
    Order::factory()->paid()->cancelled()->create(['total' => 30, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-04 12:00:00']);
    Order::factory()->unpaid()->pending()->create(['total' => 20, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-05 12:00:00']);
    Order::factory()->partiallyPaid()->confirmed()->create(['total' => 10, 'delivery_date' => '2026-09-10', 'created_at' => '2026-09-06 12:00:00']);
});

/** The Orders revenue line of the tax summary, in dollars. */
function taxSummaryOrderRevenue(string $from, string $to): string
{
    $handle = fopen('php://memory', 'r+');
    resolve(TaxCsvExporter::class)->writeSummaryCsv($handle, $from, $to);
    rewind($handle);
    $csv = (string) stream_get_contents($handle);
    fclose($handle);

    preg_match('/^"Total Revenue \(Orders\)","?([\d,.]+)"?$/m', $csv, $matches);

    return $matches[1] ?? '';
}

test('the same orders give the same revenue on every screen', function () {
    $range = DateRange::fromStrings('2026-09-07', '2026-09-13');
    $expected = Money::fromDollars(150);

    $dashboard = RevenueQuery::total($range);
    $sales = resolve(SalesReport::class)->generate($range)->totalRevenue;
    $financial = resolve(FinancialCalculator::class)->calculate(2026)->totalRevenue;
    $digest = resolve(WeeklyDigestDataCollector::class)->collect()->stats['total_revenue'];
    $goal = (new GoalTrackerWidget)->monthlyData['revenue'];
    $tax = taxSummaryOrderRevenue('2026-09-01', '2026-09-30');

    expect($dashboard)->toEqual($expected)
        ->and($sales)->toEqual($expected)
        ->and($financial)->toEqual($expected)
        ->and($digest)->toEqual($expected)
        ->and($goal)->toBe(150.0)
        ->and($tax)->toBe('150.00');
});

test('the yearly goal ignores unpaid, part-paid, refunded and cancelled orders', function () {
    expect((new GoalTrackerWidget)->yearlyData['revenue'])->toBe(150.0);
});

test('revenue is dated by delivery date, not by when the order was placed', function () {
    Order::factory()->paid()->confirmed()->create(['total' => 40, 'delivery_date' => '2026-10-02', 'created_at' => '2026-09-12 12:00:00']);

    $range = DateRange::fromStrings('2026-09-01', '2026-09-30');

    expect(RevenueQuery::total($range))->toEqual(Money::fromDollars(150))
        ->and((new GoalTrackerWidget)->monthlyData['revenue'])->toBe(150.0)
        ->and(taxSummaryOrderRevenue('2026-09-01', '2026-09-30'))->toBe('150.00');
});
