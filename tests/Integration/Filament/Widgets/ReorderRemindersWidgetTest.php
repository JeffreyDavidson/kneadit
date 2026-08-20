<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\ReorderRemindersWidget;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;

beforeEach(function () {
    setUpTenantTest();
    Cache::flush();
    Date::setTestNow('2026-08-17 12:00:00');
    config(['analytics.at_risk_threshold_days' => 30]);
});

afterEach(function () {
    Cache::flush();
    Date::setTestNow();
});

test('lists only repeat customers whose latest non-cancelled order is before the cutoff', function () {
    $lapsed = Customer::factory()->create(['name' => 'Lapsed Customer']);
    Order::factory()->count(2)->for($lapsed)->create([
        'status' => OrderStatus::Delivered,
        'created_at' => Date::now()->subDays(45),
    ]);

    $recent = Customer::factory()->create(['name' => 'Recent Customer']);
    Order::factory()->for($recent)->create([
        'status' => OrderStatus::Delivered,
        'created_at' => Date::now()->subDays(45),
    ]);
    Order::factory()->for($recent)->create([
        'status' => OrderStatus::Confirmed,
        'created_at' => Date::now()->subDays(5),
    ]);

    $cancelledOnly = Customer::factory()->create(['name' => 'Cancelled Customer']);
    Order::factory()->count(2)->for($cancelledOnly)->create([
        'status' => OrderStatus::Cancelled,
        'created_at' => Date::now()->subDays(45),
    ]);

    $widget = new ReorderRemindersWidget;

    expect(ReorderRemindersWidget::canView())->toBeTrue()
        ->and($widget->getLapsedCount())->toBe(1)
        ->and($widget->getLapsedCustomers())->toHaveCount(1)
        ->and($widget->getLapsedCustomers()[0]['name'])->toBe('Lapsed Customer');
});
