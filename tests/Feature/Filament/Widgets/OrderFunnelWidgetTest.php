<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\OrderFunnelWidget;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Cache::flush();
});

test('order funnel widget renders with order data across statuses', function () {
    $customer = Customer::factory()->create();

    Order::factory()->recycle($customer)->create(['status' => OrderStatus::Pending]);
    Order::factory()->recycle($customer)->create(['status' => OrderStatus::Confirmed]);
    Order::factory()->recycle($customer)->create(['status' => OrderStatus::Ready]);

    Livewire::test(OrderFunnelWidget::class)
        ->assertOk();
});

test('order funnel widget renders with no orders', function () {
    Livewire::test(OrderFunnelWidget::class)
        ->assertOk();
});
