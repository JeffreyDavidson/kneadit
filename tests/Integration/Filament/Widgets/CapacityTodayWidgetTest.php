<?php

use App\Filament\Widgets\CapacityTodayWidget;
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

test('counts today as the bakery-local day', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');
    Order::factory()->create(['delivery_date' => '2026-10-05']);

    $today = (new CapacityTodayWidget)->getTodayCapacity();

    expect($today['current'])->toBe(1);
});
