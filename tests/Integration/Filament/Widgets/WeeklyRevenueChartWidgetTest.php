<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\WeeklyRevenueChartWidget;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    // 10:00 pm on Sunday Oct 11 in New York, which is already Monday in UTC.
    Date::setTestNow('2026-10-12 02:00');
});

/** @return array{datasets: list<array{data: list<float>}>, labels: list<string>} */
function weeklyChartData(): array
{
    $widget = new WeeklyRevenueChartWidget;

    return new ReflectionMethod($widget, 'getData')->invoke($widget);
}

test('shows the bakery-local week, ending on the last bakery-local Sunday', function () {
    Order::factory()->paid()->create(['delivery_date' => '2026-10-11', 'status' => OrderStatus::Confirmed, 'total' => 40]);

    $chart = weeklyChartData();

    expect($chart['labels'])->toBe(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])
        ->and($chart['datasets'][0]['data'])->toBe([0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 40.0]);
});

test('leaves out revenue delivered in the following bakery-local week', function () {
    Order::factory()->paid()->create(['delivery_date' => '2026-10-12', 'status' => OrderStatus::Confirmed, 'total' => 40]);

    $chart = weeklyChartData();

    expect(array_sum($chart['datasets'][0]['data']))->toBe(0.0);
});
