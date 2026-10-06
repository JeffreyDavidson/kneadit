<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\RevenueChartWidget;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
});

test('ends the window on the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    Order::factory()->paid()->create([
        'delivery_date' => '2026-10-05',
        'status' => OrderStatus::Confirmed,
        'total' => 40,
    ]);
    $widget = new RevenueChartWidget;

    $chart = new ReflectionMethod($widget, 'getData')->invoke($widget);

    expect(last($chart['labels']))->toBe('Oct 5')
        ->and(last($chart['datasets'][0]['data']))->toBe(40.0);
});

test('names a design-system colour token for each dataset so the chart follows the theme', function () {
    $widget = new RevenueChartWidget;

    $chart = new ReflectionMethod($widget, 'getData')->invoke($widget);

    expect(array_column($chart['datasets'], 'colorToken'))->toBe(['--kn-honey', '--kn-muted']);
});
