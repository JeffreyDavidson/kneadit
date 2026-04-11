<?php

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
    $this->customer = Customer::factory()->create();
});

test('can render the orders list page', function () {
    Livewire::test(ListOrders::class)
        ->assertOk();
});

test('can list orders in the table', function () {
    $orders = Order::factory()->recycle($this->customer)->count(3)->create();

    Livewire::test(ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

test('can render table columns', function (string $column) {
    Order::factory()->recycle($this->customer)->create();

    Livewire::test(ListOrders::class)
        ->assertCanRenderTableColumn($column);
})->with(['order_number', 'customer.name', 'status', 'payment_status', 'total', 'delivery_date']);

test('can render the view order page', function () {
    $order = Order::factory()->recycle($this->customer)->create();

    Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk();
});

test('can search orders by order number', function () {
    $target = Order::factory()->recycle($this->customer)->create();
    $other = Order::factory()->recycle($this->customer)->create();

    Livewire::test(ListOrders::class)
        ->searchTable($target->order_number)
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter orders by status', function () {
    $pending = Order::factory()->recycle($this->customer)->create();
    $delivered = Order::factory()->recycle($this->customer)->delivered()->create();

    Livewire::test(ListOrders::class)
        ->filterTable('status', App\Enums\Orders\OrderStatus::Delivered->value)
        ->assertCanSeeTableRecords(collect([$delivered]))
        ->assertCanNotSeeTableRecords(collect([$pending]));
});

test('can filter orders by payment status', function () {
    $unpaid = Order::factory()->recycle($this->customer)->create();
    $paid = Order::factory()->recycle($this->customer)->paid()->create();

    Livewire::test(ListOrders::class)
        ->filterTable('payment_status', App\Enums\Orders\PaymentStatus::Paid->value)
        ->assertCanSeeTableRecords(collect([$paid]))
        ->assertCanNotSeeTableRecords(collect([$unpaid]));
});

test('can sort orders by total', function () {
    $cheap = Order::factory()->recycle($this->customer)->create(['subtotal' => 10, 'total' => 10]);
    $expensive = Order::factory()->recycle($this->customer)->create(['subtotal' => 100, 'total' => 100]);

    Livewire::test(ListOrders::class)
        ->sortTable('total')
        ->assertCanSeeTableRecords(collect([$cheap, $expensive]), inOrder: true)
        ->sortTable('total', 'desc')
        ->assertCanSeeTableRecords(collect([$expensive, $cheap]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\Orders\OrderResource::getGloballySearchableAttributes())
        ->toBe(['customer.name', 'customer.email', 'status']);
});

test('resource returns global search result title', function () {
    $order = Order::factory()->recycle($this->customer)->create();

    expect(App\Filament\Resources\Orders\OrderResource::getGlobalSearchResultTitle($order))
        ->toBe('Order #' . $order->order_number);
});

test('resource returns global search result details', function () {
    $order = Order::factory()->recycle($this->customer)->create(['total' => 99.99]);

    $details = App\Filament\Resources\Orders\OrderResource::getGlobalSearchResultDetails($order);

    expect($details)
        ->toHaveKey('Customer')
        ->toHaveKey('Total')
        ->toHaveKey('Status');
});

test('global search eloquent query eager loads customer', function () {
    $query = App\Filament\Resources\Orders\OrderResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('customer');
});
