<?php

use App\Filament\Widgets\TodaysOrdersWidget;
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

test('treats the bakery-local day as today', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    $today = Order::factory()->create(['delivery_date' => '2026-10-05']);
    Order::factory()->create(['delivery_date' => '2026-10-06']);

    $canView = TodaysOrdersWidget::canView();
    $rows = (new TodaysOrdersWidget)->getOrderRows();

    expect($canView)->toBeTrue()
        ->and($rows)->toHaveCount(1)
        ->and($rows[0]['id'])->toBe($today->id);
});

test('keeps today as the UTC day when no bakery timezone is set', function () {
    Date::setTestNow('2026-10-06 01:00');
    $today = Order::factory()->create(['delivery_date' => '2026-10-06']);
    Order::factory()->create(['delivery_date' => '2026-10-05']);

    $rows = (new TodaysOrdersWidget)->getOrderRows();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['id'])->toBe($today->id);
});
