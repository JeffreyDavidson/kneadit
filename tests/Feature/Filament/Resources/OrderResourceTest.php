<?php

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    test()->customer = Customer::factory()->create();
});

test('can render the orders list page', function () {
    livewire(ListOrders::class)
        ->assertOk();
});

test('can list orders in the table', function () {
    $orders = Order::factory()->recycle(testFixture('customer', Customer::class))->count(3)->create();

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

test('can render table columns', function (string $column) {
    Order::factory()->recycle(testFixture('customer', Customer::class))->create();

    livewire(ListOrders::class)
        ->assertCanRenderTableColumn('order_number')
        ->assertCanRenderTableColumn('customer.name')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('payment_status')
        ->assertCanRenderTableColumn('total')
        ->assertCanRenderTableColumn('delivery_date');
});

test('can render the view order page', function () {
    $order = Order::factory()->recycle(testFixture('customer', Customer::class))->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertSee('Your bread is ready.')
        ->assertSee('Thank you, see you soon.')
        ->assertSee('flex justify-end', false)
        ->assertSee('flex justify-start', false);
});

test('view order page renders the Catering section when the order is linked to an inquiry', function () {
    $inquiry = CateringInquiry::factory()->create([
        'event_type' => 'Wedding',
        'guest_count' => 120,
        'venue_address' => '123 Beachfront Way',
    ]);
    $order = Order::factory()->recycle(testFixture('customer', Customer::class))->for($inquiry, 'cateringInquiry')->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertSee('Catering')
        ->assertSee('Wedding')
        ->assertSee('120')
        ->assertSee('123 Beachfront Way');
});

test('view order page omits the Catering section for non-catering orders', function () {
    $order = Order::factory()->recycle(testFixture('customer', Customer::class))->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertDontSee('View inquiry');
});

test('can search orders by order number', function () {
    $target = Order::factory()->recycle(testFixture('customer', Customer::class))->create();
    $other = Order::factory()->recycle(testFixture('customer', Customer::class))->create();

    livewire(ListOrders::class)
        ->searchTable($target->order_number)
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter orders by status', function () {
    $pending = Order::factory()->recycle(testFixture('customer', Customer::class))->create();
    $delivered = Order::factory()->recycle(testFixture('customer', Customer::class))->delivered()->create();

    livewire(ListOrders::class)
        ->filterTable('status', App\Enums\Orders\OrderStatus::Delivered->value)
        ->assertCanSeeTableRecords(collect([$delivered]))
        ->assertCanNotSeeTableRecords(collect([$pending]));
});

test('can filter orders by payment status', function () {
    $unpaid = Order::factory()->recycle(testFixture('customer', Customer::class))->create();
    $paid = Order::factory()->recycle(testFixture('customer', Customer::class))->paid()->create();

    livewire(ListOrders::class)
        ->filterTable('payment_status', App\Enums\Orders\PaymentStatus::Paid->value)
        ->assertCanSeeTableRecords(collect([$paid]))
        ->assertCanNotSeeTableRecords(collect([$unpaid]));
});

test('can sort orders by total', function () {
    $cheap = Order::factory()->recycle(testFixture('customer', Customer::class))->create(['subtotal' => 10, 'total' => 10]);
    $expensive = Order::factory()->recycle(testFixture('customer', Customer::class))->create(['subtotal' => 100, 'total' => 100]);

    livewire(ListOrders::class)
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
    $order = Order::factory()->recycle(testFixture('customer', Customer::class))->create();

    expect(App\Filament\Resources\Orders\OrderResource::getGlobalSearchResultTitle($order))
        ->toBe('Order #' . $order->order_number);
});

test('resource returns global search result details', function () {
    $order = Order::factory()->recycle(testFixture('customer', Customer::class))->create(['total' => 99.99]);

    $details = App\Filament\Resources\Orders\OrderResource::getGlobalSearchResultDetails($order);

    expect($details)
        ->toHaveKeys(['Customer', 'Total', 'Status']);
});

test('global search eloquent query eager loads customer', function () {
    $query = App\Filament\Resources\Orders\OrderResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('customer');
});
