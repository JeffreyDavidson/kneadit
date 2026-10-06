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
use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Product;
use App\Models\Inventory\Recipe;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\Stripe\FakeRefundStripeClient;

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
        ->assertSeeHtml('bg-(--kn-warning-tint)')
        ->assertSeeHtml('bg-(--kn-danger-tint)')
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

test('the markPaid table action is only visible for unpaid or partially paid orders that are not cancelled', function (string $state, bool $visible) {
    $order = Order::factory()->recycle(test()->customer)->{$state}()->create();

    $assertion = $visible ? 'assertActionVisible' : 'assertActionHidden';

    livewire(ListOrders::class)
        ->{$assertion}(TestAction::make('markPaid')->table($order));
})->with([
    'unpaid pending' => ['pending', true],
    'unpaid confirmed' => ['confirmed', true],
    'partially paid' => ['partiallyPaid', true],
    'paid' => ['paid', false],
    'cancelled' => ['cancelled', false],
]);

test('the markPaid table action marks a partially paid catering order as paid', function () {
    $order = Order::factory()->recycle(test()->customer)->confirmed()->partiallyPaid()->create();

    livewire(ListOrders::class)
        ->callAction(TestAction::make('markPaid')->table($order))
        ->assertNotified();

    expect($order->refresh())
        ->payment_status->toBe(PaymentStatus::Paid)
        ->status->toBe(OrderStatus::Confirmed);
});

test('the markPaid header action on the view order page marks the order as paid', function () {
    $order = Order::factory()->recycle(test()->customer)->create(['payment_method' => PaymentMethod::Cash]);

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('markPaid')
        ->assertNotified();

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('the markPaid header action on the view order page marks a partially paid catering order as paid', function () {
    $order = Order::factory()->recycle(test()->customer)->confirmed()->partiallyPaid()->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertActionVisible('markPaid')
        ->callAction('markPaid')
        ->assertNotified();

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
});

test('the markPaid header action on the view order page is hidden for paid and cancelled orders', function (string $state) {
    $order = Order::factory()->recycle(test()->customer)->{$state}()->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertActionHidden('markPaid');
})->with(['paid', 'cancelled']);

dataset('orderMoneyFields', [
    'subtotal' => 'subtotal',
    'delivery fee' => 'delivery_fee',
    'discount' => 'discount_amount',
    'gift card amount' => 'gift_card_amount',
    'tip' => 'tip_amount',
    'total' => 'total',
]);

test('editing an order cannot change its money fields', function (string $field) {
    $order = Order::factory()->recycle(test()->customer)->create([
        'subtotal' => 30,
        'delivery_fee' => 5,
        'discount_amount' => 3,
        'gift_card_amount' => 2,
        'tip_amount' => 4,
        'total' => 34,
    ]);

    livewire(ListOrders::class)
        ->callAction(TestAction::make('edit')->table($order), data: [$field => 999, 'notes' => 'Edited'])
        ->assertHasNoFormErrors();

    expect($order->refresh())
        ->notes->toBe('Edited')
        ->subtotal->dollars()->toBe(30.0)
        ->delivery_fee->dollars()->toBe(5.0)
        ->discount_amount->dollars()->toBe(3.0)
        ->gift_card_amount->dollars()->toBe(2.0)
        ->tip_amount->dollars()->toBe(4.0)
        ->total->dollars()->toBe(34.0);
})->with('orderMoneyFields');

test('an order created from the admin form has its total computed from the other amounts', function () {
    $baker = User::factory()->create();

    livewire(ListOrders::class)
        ->callAction('create', data: [
            'order_number' => 'ORD-ADMIN-2',
            'customer_id' => test()->customer->id,
            'payment_status' => PaymentStatus::Unpaid->value,
            'payment_method' => PaymentMethod::Cash->value,
            'user_id' => $baker->id,
            'subtotal' => 40,
            'delivery_fee' => 5,
            'discount_amount' => 10,
            'gift_card_amount' => 5,
            'tip_amount' => 2,
            'total' => 1,
        ])
        ->assertHasNoFormErrors();

    expect(Order::query()->where('order_number', 'ORD-ADMIN-2')->sole()->total->dollars())->toBe(32.0);
});

function makeStripePaidOrder(array $attributes = []): Order
{
    return Order::factory()->recycle(test()->customer)->confirmed()->paid()->create([
        'stripe_payment_intent_id' => 'pi_resource_test',
        'payment_method' => PaymentMethod::Stripe,
        'total' => 25.00,
        ...$attributes,
    ]);
}

test('staff can cancel an unpaid order from the table and the order page', function (string $surface) {
    test()->actingAs(User::factory()->staff()->create());
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->recycle(test()->customer)->confirmed()->unpaid()->create();

    $surface === 'table'
        ? livewire(ListOrders::class)->callAction(TestAction::make('cancel')->table($order), data: ['reason' => 'No longer needed'])->assertNotified()
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()])->callAction('cancel', data: ['reason' => 'No longer needed'])->assertNotified();

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
    $stripe->unused();
})->with(['table', 'order page']);

test('staff cannot cancel an order that has been paid', function (string $state, string $surface) {
    test()->actingAs(User::factory()->staff()->create());
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->recycle(test()->customer)->confirmed()->{$state}()->create(['stripe_payment_intent_id' => 'pi_staff']);

    $component = $surface === 'table'
        ? livewire(ListOrders::class)
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()]);
    $action = $surface === 'table' ? TestAction::make('cancel')->table($order) : 'cancel';

    $component
        ->assertActionHidden($action)
        ->mountAction($action)
        ->callMountedAction();

    expect($order->refresh())
        ->status->toBe(OrderStatus::Confirmed)
        ->payment_status->not->toBe(PaymentStatus::Refunded);
    $stripe->unused();
})->with(['paid', 'partiallyPaid'])->with(['table', 'order page']);

test('a manager cancelling a paid Stripe order refunds it once and ends cancelled and refunded', function (string $surface) {
    test()->actingAs(User::factory()->manager()->create());
    $stripe = FakeRefundStripeClient::succeeding('re_resource_cancel');
    $order = makeStripePaidOrder();

    $surface === 'table'
        ? livewire(ListOrders::class)->callAction(TestAction::make('cancel')->table($order), data: ['reason' => 'Customer asked'])->assertNotified('Order cancelled and refunded')
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()])->callAction('cancel', data: ['reason' => 'Customer asked'])->assertNotified('Order cancelled and refunded');

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Refunded)
        ->and(Refund::query()->where('order_id', $order->id)->sole()->stripe_refund_id)->toBe('re_resource_cancel');
    $stripe->verify();
})->with(['table', 'order page']);

test('a Stripe refusal leaves the order uncancelled and tells the manager', function (string $surface) {
    test()->actingAs(User::factory()->manager()->create());
    $stripe = FakeRefundStripeClient::refusing();
    $order = makeStripePaidOrder();

    $surface === 'table'
        ? livewire(ListOrders::class)->callAction(TestAction::make('cancel')->table($order))->assertNotified('Stripe could not refund this order, so it was not cancelled')
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()])->callAction('cancel')->assertNotified('Stripe could not refund this order, so it was not cancelled');

    expect($order->refresh())
        ->status->toBe(OrderStatus::Confirmed)
        ->payment_status->toBe(PaymentStatus::Paid)
        ->and(Refund::query()->count())->toBe(0);
    $stripe->verify();
})->with(['table', 'order page']);

test('cancelling a paid order not taken through Stripe leaves it paid and says to refund it by hand', function () {
    test()->actingAs(User::factory()->manager()->create());
    $stripe = FakeRefundStripeClient::untouched();
    $order = makeStripePaidOrder(['stripe_payment_intent_id' => null, 'payment_method' => PaymentMethod::Cash]);

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('cancel')
        ->assertNotified('Order cancelled. Refund the customer outside Stripe');

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Paid);
    $stripe->unused();
});

test('the order page status change no longer offers cancelling', function () {
    $order = Order::factory()->recycle(test()->customer)->confirmed()->unpaid()->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('changeStatus', data: ['status' => OrderStatus::Cancelled->value])
        ->assertHasFormErrors(['status']);

    expect($order->refresh()->status)->toBe(OrderStatus::Confirmed);
});

test('the order page status change still moves an order forward', function () {
    $order = Order::factory()->recycle(test()->customer)->confirmed()->unpaid()->create();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('changeStatus', data: ['status' => OrderStatus::Baking->value])
        ->assertHasNoFormErrors();

    expect($order->refresh()->status)->toBe(OrderStatus::Baking);
});

test('starting to bake with short stock still succeeds and warns the person who clicked', function (string $surface) {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['name' => 'Rye Flour', 'unit' => 'kg', 'current_stock' => 0.5]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.7, 'unit' => 'kg']);
    $order = Order::factory()->recycle(test()->customer)->confirmed()->unpaid()->create(['order_number' => 'ORD-SHORT']);
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 1]);

    $component = $surface === 'table'
        ? livewire(ListOrders::class)->callAction(TestAction::make('start_baking')->table($order))
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()])->callAction('changeStatus', data: ['status' => OrderStatus::Baking->value]);

    $component->assertNotified('Order ORD-SHORT is baking without enough stock');
    expect($order->refresh()->status)->toBe(OrderStatus::Baking)
        ->and($flour->fresh()->current_stock)->toBe('-0.2000');
})->with(['table', 'order page']);

test('starting to bake with enough stock shows no stock warning', function () {
    $product = Product::factory()->create();
    $recipe = Recipe::factory()->for($product)->create();
    $flour = Ingredient::factory()->create(['unit' => 'kg', 'current_stock' => 5]);
    $recipe->inventoryIngredients()->attach($flour->id, ['quantity' => 0.7, 'unit' => 'kg']);
    $order = Order::factory()->recycle(test()->customer)->confirmed()->unpaid()->create(['order_number' => 'ORD-ENOUGH']);
    OrderItem::factory()->for($order)->for($product)->create(['quantity' => 1]);

    livewire(ListOrders::class)
        ->callAction(TestAction::make('start_baking')->table($order))
        ->assertNotNotified('Order ORD-ENOUGH is baking without enough stock');
});

test('a manager can refund an order that was cancelled while still paid', function (string $surface) {
    test()->actingAs(User::factory()->manager()->create());
    $stripe = FakeRefundStripeClient::succeeding('re_resource_refund');
    $order = makeStripePaidOrder(['status' => OrderStatus::Cancelled]);

    $surface === 'table'
        ? livewire(ListOrders::class)->callAction(TestAction::make('refund')->table($order), data: ['reason' => 'Cancelled earlier'])->assertNotified('Order refunded')
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()])->callAction('refund', data: ['reason' => 'Cancelled earlier'])->assertNotified('Order refunded');

    expect($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Refunded)
        ->and(Refund::query()->where('order_id', $order->id)->sole()->stripe_refund_id)->toBe('re_resource_refund');
    $stripe->verify();
})->with(['table', 'order page']);

test('a refused refund leaves the cancelled order paid and tells the manager', function () {
    test()->actingAs(User::factory()->manager()->create());
    $stripe = FakeRefundStripeClient::refusing();
    $order = makeStripePaidOrder(['status' => OrderStatus::Cancelled]);

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->callAction('refund')
        ->assertNotified('Stripe could not refund this order');

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
    $stripe->verify();
});

test('staff cannot refund an order', function (string $surface) {
    test()->actingAs(User::factory()->staff()->create());
    $stripe = FakeRefundStripeClient::untouched();
    $order = makeStripePaidOrder(['status' => OrderStatus::Cancelled]);

    $component = $surface === 'table'
        ? livewire(ListOrders::class)
        : livewire(ViewOrder::class, ['record' => $order->getRouteKey()]);
    $action = $surface === 'table' ? TestAction::make('refund')->table($order) : 'refund';

    $component
        ->assertActionHidden($action)
        ->mountAction($action)
        ->callMountedAction();

    expect($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
    $stripe->unused();
})->with(['table', 'order page']);

test('the refund action is only offered on cancelled orders paid through Stripe', function (array $attributes) {
    test()->actingAs(User::factory()->manager()->create());
    $order = makeStripePaidOrder($attributes);

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertActionHidden('refund');
})->with([
    'not cancelled' => [[]],
    'already refunded' => [['status' => OrderStatus::Cancelled, 'payment_status' => PaymentStatus::Refunded]],
    'not paid' => [['status' => OrderStatus::Cancelled, 'payment_status' => PaymentStatus::Unpaid]],
    'not through Stripe' => [['status' => OrderStatus::Cancelled, 'stripe_payment_intent_id' => null]],
]);
