<?php

use App\Filament\Widgets\AtRiskCustomersWidget;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Date::setTestNow('2026-10-06 12:00');
});

test('rows show each at-risk customer lifetime value, last order and days inactive', function () {
    $customer = Customer::factory()->create(['name' => 'Quiet Quinn']);
    Order::factory()->paid()->recycle($customer)->create(['total' => 50.00, 'created_at' => now()->subDays(90)]);
    Order::factory()->paid()->recycle($customer)->create(['total' => 30.00, 'created_at' => now()->subDays(60)]);

    $rows = (new AtRiskCustomersWidget)->getRows();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['name'])->toBe('Quiet Quinn')
        ->and($rows[0]['days_inactive'])->toBe(60)
        ->and($rows[0]['lifetime_value'])->toBe('$80')
        ->and($rows[0]['last_order'])->not->toBe('Never');
});

test('lifetime value counts only paid orders that were not cancelled', function () {
    $customer = Customer::factory()->create();
    Order::factory()->paid()->recycle($customer)->create(['total' => 40.00, 'created_at' => now()->subDays(50)]);
    Order::factory()->unpaid()->recycle($customer)->create(['total' => 25.00, 'created_at' => now()->subDays(45)]);
    Order::factory()->paid()->cancelled()->recycle($customer)->create(['total' => 99.00, 'created_at' => now()->subDays(40)]);

    $rows = (new AtRiskCustomersWidget)->getRows();

    expect($rows[0]['lifetime_value'])->toBe('$40')
        ->and($rows[0]['days_inactive'])->toBe(45);
});

test('rows list the customer who has been quiet the longest first', function () {
    $recent = Customer::factory()->create(['name' => 'Recent Rae']);
    $longest = Customer::factory()->create(['name' => 'Longest Lee']);
    $middle = Customer::factory()->create(['name' => 'Middle Max']);
    Order::factory()->paid()->recycle($recent)->create(['created_at' => now()->subDays(35)]);
    Order::factory()->paid()->recycle($longest)->create(['created_at' => now()->subDays(120)]);
    Order::factory()->paid()->recycle($middle)->create(['created_at' => now()->subDays(70)]);

    $rows = (new AtRiskCustomersWidget)->getRows();

    expect(array_column($rows, 'name'))->toBe(['Longest Lee', 'Middle Max', 'Recent Rae'])
        ->and(array_column($rows, 'days_inactive'))->toBe([120, 70, 35]);
});
