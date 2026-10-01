<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Events\Orders\OrderDelivered;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

use function Pest\Livewire\livewire;

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
    $orders = Order::factory()->recycle(test()->customer)->count(3)->create();

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders);
});

test('can render table columns', function () {
    Order::factory()->recycle(test()->customer)->create();

    livewire(ListOrders::class)
        ->assertCanRenderTableColumn('order_number')
        ->assertCanRenderTableColumn('customer.name')
        ->assertCanRenderTableColumn('status')
        ->assertCanRenderTableColumn('payment_status')
        ->assertCanRenderTableColumn('total')
        ->assertCanRenderTableColumn('delivery_date');
});

test('bulk deleting orders only removes those nothing financial happened on', function () {
    test()->actingAs(User::factory()->manager()->create());
    $deletable = Order::factory()->recycle(test()->customer)->pending()->unpaid()->create();
    $paid = Order::factory()->recycle(test()->customer)->delivered()->create();
    $refund = Refund::factory()->for($paid)->create();

    livewire(ListOrders::class)
        ->selectTableRecords([$deletable, $paid])
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified(
            Notification::make()
                ->warning()
                ->title('Deleted 1 of 2')
                ->body('<p>1 not deleted. Only managers can delete orders, and only pending or cancelled orders with no payment or refund. Cancel the rest instead.</p>')
                ->persistent(),
        );

    expect(Order::query()->find($deletable->id))->toBeNull()
        ->and(Order::query()->find($paid->id))->not->toBeNull()
        ->and(Refund::query()->find($refund->id))->not->toBeNull();
});

test('bulk deleting orders is not allowed for staff', function () {
    test()->actingAs(User::factory()->staff()->create());
    $order = Order::factory()->recycle(test()->customer)->pending()->unpaid()->create();

    livewire(ListOrders::class)
        ->selectTableRecords([$order])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Order::query()->find($order->id))->not->toBeNull();
});

test('can render the view order page', function () {
    $order = Order::factory()->recycle(test()->customer)->create();
    OrderItem::factory()->for($order)->create([
        'name' => 'Custom bread',
        'quantity' => 2,
        'unit_price' => 12.50,
    ]);
    OrderMessage::factory()->for($order)->fromBaker()->create(['message' => 'Your bread is ready.']);
    OrderMessage::factory()->for($order)->fromCustomer()->create(['message' => 'Thank you, see you soon.']);

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertSee('Your bread is ready.')
        ->assertSee('Thank you, see you soon.')
        ->assertSee('Custom bread')
        ->assertSee('$25.00')
        ->assertSeeHtml('bg-amber-500/15')
        ->assertSeeHtml('bg-red-500/15')
        ->assertSeeHtml('flex justify-end')
        ->assertSeeHtml('flex justify-start');
});

test('view order page renders the Catering section when the order is linked to an inquiry', function () {
    $inquiry = CateringInquiry::factory()->create([
        'event_type' => 'Wedding',
        'guest_count' => 120,
        'venue_address' => '123 Beachfront Way',
    ]);
    $order = Order::factory()->recycle(test()->customer)->for($inquiry, 'cateringInquiry')->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertSee('Catering')
        ->assertSee('Wedding')
        ->assertSee('120')
        ->assertSee('123 Beachfront Way');
});

test('view order page omits the Catering section for non-catering orders', function () {
    $order = Order::factory()->recycle(test()->customer)->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertOk()
        ->assertDontSee('View inquiry');
});

test('can search orders by order number', function () {
    $target = Order::factory()->recycle(test()->customer)->create();
    $other = Order::factory()->recycle(test()->customer)->create();

    livewire(ListOrders::class)
        ->searchTable($target->order_number)
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter orders by status', function () {
    $pending = Order::factory()->recycle(test()->customer)->create();
    $delivered = Order::factory()->recycle(test()->customer)->delivered()->create();

    livewire(ListOrders::class)
        ->filterTable('status', OrderStatus::Delivered->value)
        ->assertCanSeeTableRecords(collect([$delivered]))
        ->assertCanNotSeeTableRecords(collect([$pending]));
});

test('can filter orders by payment status', function () {
    $unpaid = Order::factory()->recycle(test()->customer)->create();
    $paid = Order::factory()->recycle(test()->customer)->paid()->create();

    livewire(ListOrders::class)
        ->filterTable('payment_status', PaymentStatus::Paid->value)
        ->assertCanSeeTableRecords(collect([$paid]))
        ->assertCanNotSeeTableRecords(collect([$unpaid]));
});

test('can sort orders by total', function () {
    $cheap = Order::factory()->recycle(test()->customer)->create(['subtotal' => 10, 'total' => 10]);
    $expensive = Order::factory()->recycle(test()->customer)->create(['subtotal' => 100, 'total' => 100]);

    livewire(ListOrders::class)
        ->sortTable('total')
        ->assertCanSeeTableRecords(collect([$cheap, $expensive]), inOrder: true)
        ->sortTable('total', 'desc')
        ->assertCanSeeTableRecords(collect([$expensive, $cheap]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(OrderResource::getGloballySearchableAttributes())
        ->toBe(['customer.name', 'customer.email', 'status']);
});

test('resource returns global search result title', function () {
    $order = Order::factory()->recycle(test()->customer)->create();

    expect(OrderResource::getGlobalSearchResultTitle($order))
        ->toBe('Order #'.$order->order_number);
});

test('resource returns global search result details', function () {
    $order = Order::factory()->recycle(test()->customer)->create(['total' => 99.99]);

    $details = OrderResource::getGlobalSearchResultDetails($order);

    expect($details)
        ->toHaveKeys(['Customer', 'Total', 'Status']);
});

test('global search eloquent query eager loads customer', function () {
    $query = OrderResource::getGlobalSearchEloquentQuery();

    expect($query->getEagerLoads())->toHaveKey('customer');
});

test('editing an order cannot change its status', function () {
    Event::fake([OrderDelivered::class]);
    $order = Order::factory()->recycle(test()->customer)->create();

    livewire(ListOrders::class)
        ->callAction(TestAction::make('edit')->table($order), data: ['status' => OrderStatus::Delivered->value])
        ->assertHasNoFormErrors();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
    Event::assertNotDispatched(OrderDelivered::class);
});

test('editing an order cannot change its payment status', function () {
    $order = Order::factory()->recycle(test()->customer)->create();

    livewire(ListOrders::class)
        ->callAction(TestAction::make('edit')->table($order), data: ['payment_status' => PaymentStatus::Paid->value])
        ->assertHasNoFormErrors();

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Unpaid);
});

test('editing an order still saves its other fields', function () {
    $order = Order::factory()->recycle(test()->customer)->create();

    livewire(ListOrders::class)
        ->callAction(TestAction::make('edit')->table($order), data: ['notes' => 'Leave at the side door'])
        ->assertHasNoFormErrors();

    expect($order->refresh()->notes)->toBe('Leave at the side door');
});

test('an order created from the admin form always starts pending', function (OrderStatus $submitted) {
    $baker = User::factory()->create();

    livewire(ListOrders::class)
        ->callAction('create', data: [
            'order_number' => 'ORD-ADMIN-1',
            'customer_id' => test()->customer->id,
            'status' => $submitted->value,
            'payment_status' => PaymentStatus::Unpaid->value,
            'payment_method' => PaymentMethod::Cash->value,
            'user_id' => $baker->id,
            'subtotal' => 25,
            'total' => 25,
        ])
        ->assertHasNoFormErrors();

    expect(Order::query()->where('order_number', 'ORD-ADMIN-1')->sole()->status)->toBe(OrderStatus::Pending);
})->with([
    'delivered' => OrderStatus::Delivered,
    'cancelled' => OrderStatus::Cancelled,
    'pending' => OrderStatus::Pending,
]);

test('the markPaid table action marks a manual-payment order as paid without confirming it', function () {
    $order = Order::factory()->recycle(test()->customer)->create(['payment_method' => PaymentMethod::Cash]);

    livewire(ListOrders::class)
        ->callAction(TestAction::make('markPaid')->table($order))
        ->assertNotified();

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Pending);
});

test('the markPaid table action auto-confirms a pending order paid by a non-manual method', function () {
    $order = Order::factory()->recycle(test()->customer)->create(['payment_method' => PaymentMethod::Stripe]);

    livewire(ListOrders::class)
        ->callAction(TestAction::make('markPaid')->table($order));

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('the markPaid table action is only visible for unpaid orders that are not cancelled', function (string $state, bool $visible) {
    $order = Order::factory()->recycle(test()->customer)->{$state}()->create();

    $assertion = $visible ? 'assertActionVisible' : 'assertActionHidden';

    livewire(ListOrders::class)
        ->{$assertion}(TestAction::make('markPaid')->table($order));
})->with([
    'unpaid pending' => ['pending', true],
    'unpaid confirmed' => ['confirmed', true],
    'paid' => ['paid', false],
    'cancelled' => ['cancelled', false],
]);

test('the markPaid header action on the view order page marks the order as paid', function () {
    $order = Order::factory()->recycle(test()->customer)->create(['payment_method' => PaymentMethod::Cash]);

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('markPaid')
        ->assertNotified();

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('the markPaid header action on the view order page is hidden for paid and cancelled orders', function (string $state) {
    $order = Order::factory()->recycle(test()->customer)->{$state}()->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertActionHidden('markPaid');
})->with(['paid', 'cancelled']);
