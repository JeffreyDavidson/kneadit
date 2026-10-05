<?php

use App\Filament\Widgets\CustomerInsightsWidget;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Date;

use function Pest\Livewire\livewire;

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

    expect(test()->widget->getAvgOrderValue()['value'])->toEqual(Money::fromDollars(120));
});

test('average order value of a single 25.50 order is 25.50, not 100 times that', function () {
    Date::setTestNow('2026-10-25 12:00');
    Order::factory()->create(['total' => 25.50, 'created_at' => '2026-10-20 12:00:00']);

    livewire(CustomerInsightsWidget::class)
        ->assertSee('$25.50')
        ->assertDontSee('2,550');
});
