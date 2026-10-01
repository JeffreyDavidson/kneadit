<?php

use App\Filament\Widgets\CustomerInsightsWidget;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    test()->widget = new CustomerInsightsWidget;
});

test('new customers this week counts from the start of the bakery-local week', function () {
    Date::setTestNow('2026-10-05 02:00');
    Customer::factory()->create(['created_at' => '2026-09-30 12:00:00']);
    Customer::factory()->create(['created_at' => '2026-09-27 12:00:00']);

    expect(test()->widget->getNewCustomersThisWeek())->toBe(1);
});

test('average order value compares the bakery-local month', function () {
    Date::setTestNow('2026-11-01 01:00');
    Order::factory()->create(['total' => 120, 'created_at' => '2026-10-20 12:00:00']);

    expect(test()->widget->getAvgOrderValue()['value'])->toBe(12000.0);
});
