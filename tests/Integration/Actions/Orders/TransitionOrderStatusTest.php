<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderStatusChanged;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Inventory\InventoryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    test()->user = User::factory()->owner()->create();
});

test('transitions order from pending to confirmed', function () {
    $order = Order::factory()->pending()->create();

    $result = resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    expect($result->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('throws exception for invalid transition', function () {
    $order = Order::factory()->pending()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Ready);
})->throws(InvalidOrderTransitionException::class, 'Cannot change status from pending to ready');

test('rejects transitions from terminal delivered state', function () {
    $order = Order::factory()->delivered()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);
})->throws(InvalidOrderTransitionException::class);

test('sends status email on transition', function () {
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->create(['status' => OrderStatus::Pending]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Confirmed);
});

test('allowedTransitions returns valid next statuses', function () {
    $pending = Order::factory()->pending()->create();
    $confirmed = Order::factory()->confirmed()->create();
    $delivered = Order::factory()->delivered()->create();

    expect(TransitionOrderStatus::allowedTransitions($pending))
        ->toContain(OrderStatus::Confirmed)
        ->toContain(OrderStatus::Cancelled)
        ->and(TransitionOrderStatus::allowedTransitions($confirmed))
        ->toContain(OrderStatus::Baking)
        ->and(TransitionOrderStatus::allowedTransitions($delivered))
        ->toBeEmpty();
});

test('dispatches OrderStatusChanged after every valid transition', function () {
    Event::fake();
    $order = Order::factory()->pending()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Event::assertDispatched(
        OrderStatusChanged::class,
        fn (OrderStatusChanged $event) => $event->from === OrderStatus::Pending
            && $event->to === OrderStatus::Confirmed
            && $event->order->is($order),
    );
});

test('dispatches OrderDelivered on Ready to Delivered transition', function () {
    Event::fake();
    $order = Order::factory()->ready()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Delivered);

    Event::assertDispatched(OrderDelivered::class, fn (OrderDelivered $event) => $event->order->is($order));
    Event::assertDispatched(OrderStatusChanged::class);
});

test('dispatches OrderCancelled when cancelling', function () {
    Event::fake();
    $order = Order::factory()->confirmed()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    Event::assertDispatched(OrderCancelled::class, fn (OrderCancelled $event) => $event->order->is($order));
});

test('does not dispatch OrderDelivered or OrderCancelled on other transitions', function () {
    Event::fake();
    $order = Order::factory()->pending()->create();

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Confirmed);

    Event::assertNotDispatched(OrderDelivered::class);
    Event::assertNotDispatched(OrderCancelled::class);
});

test('critical effect failure rolls back the status transition', function () {
    $inventoryManager = Mockery::mock(InventoryManager::class);
    $inventoryManager->shouldReceive('deductForOrder')
        ->andThrow(new RuntimeException('Inventory deduction failed'));

    app()->instance(InventoryManager::class, $inventoryManager);

    $order = Order::factory()->confirmed()->create();

    try {
        resolve(TransitionOrderStatus::class)($order, OrderStatus::Baking);
        expect(false)->toBeTrue('Expected exception to be thrown');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toBe('Inventory deduction failed');
    }

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('cancellation decrements coupon used_count and creates reversal transaction', function () {
    $coupon = Coupon::factory()->create(['used_count' => 3]);
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->recycle(test()->user)
        ->confirmed()
        ->create([
            'coupon_id' => $coupon->id,
            'discount_amount' => 5.00,
        ]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    expect($coupon->fresh()->used_count)->toBe(2);

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction)->not->toBeNull()
        ->and($transaction->type)->toBe(CouponTransactionType::Reversal)
        ->and($transaction->coupon_id)->toBe($coupon->id);
});

test('cancellation restores gift card balance and creates refund transaction', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);
    $customer = Customer::factory()->create();
    $order = Order::factory()
        ->for($customer)
        ->recycle(test()->user)
        ->create([
            'status' => OrderStatus::Confirmed,
            'gift_card_id' => $giftCard->id,
            'gift_card_amount' => 20.00,
        ]);

    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    expect($giftCard->fresh()->current_balance)->toBe('50.00');

    $refund = GiftCardTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', GiftCardTransactionType::Refund)
        ->first();

    expect($refund)->not->toBeNull()
        ->and($refund->amount)->toBe('20.00')
        ->and($refund->gift_card_id)->toBe($giftCard->id);
});
