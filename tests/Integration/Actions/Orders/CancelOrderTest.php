<?php

use App\Actions\Orders\CancelOrder;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Financial\Refund;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Stripe\FakeRefundStripeClient;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('cancelling an unpaid order does not touch Stripe', function () {
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->confirmed()->unpaid()->create();

    $refund = resolve(CancelOrder::class)($order);

    expect($refund)->toBeNull()
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Unpaid);
    $stripe->unused();
});

test('cancelling a paid Stripe order refunds it once and cancels it', function () {
    $stripe = FakeRefundStripeClient::succeeding('re_cancel_paid');
    $manager = User::factory()->manager()->create();
    $order = Order::factory()->confirmed()->paid()->create([
        'stripe_payment_intent_id' => 'pi_cancel_paid',
        'total' => 25.00,
    ]);

    $refund = resolve(CancelOrder::class)($order, $manager, 'Customer asked');

    expect($refund)->toBeInstanceOf(Refund::class)
        ->and($refund->stripe_refund_id)->toBe('re_cancel_paid')
        ->and($refund->reason)->toBe('Customer asked')
        ->and($refund->user_id)->toBe($manager->id)
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Refunded);
    $stripe->verify();
});

test('a Stripe refusal leaves the order uncancelled and paid', function () {
    $stripe = FakeRefundStripeClient::refusing();
    $order = Order::factory()->confirmed()->paid()->create(['stripe_payment_intent_id' => 'pi_cancel_refused']);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(StripeRefundFailedException::class)
        ->and($order->refresh())->status->toBe(OrderStatus::Confirmed)->payment_status->toBe(PaymentStatus::Paid)
        ->and(Refund::query()->count())->toBe(0);
    $stripe->verify();
});

test('cancelling a paid order that was not paid through Stripe cancels it and leaves it paid for a manual refund', function () {
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->confirmed()->paid()->create(['stripe_payment_intent_id' => null]);

    $refund = resolve(CancelOrder::class)($order);

    expect($refund)->toBeNull()
        ->and($order->refresh())
        ->status->toBe(OrderStatus::Cancelled)
        ->payment_status->toBe(PaymentStatus::Paid);
    $stripe->unused();
});

test('an order that cannot be cancelled is not refunded', function () {
    $stripe = FakeRefundStripeClient::untouched();
    $order = Order::factory()->ready()->paid()->create(['stripe_payment_intent_id' => 'pi_cancel_ready']);

    expect(fn () => resolve(CancelOrder::class)($order))->toThrow(InvalidOrderTransitionException::class)
        ->and($order->refresh()->payment_status)->toBe(PaymentStatus::Paid);
    $stripe->unused();
});

test('cancelling credits a gift card with what was redeemed even if the order amount was tampered with', function () {
    FakeRefundStripeClient::untouched();
    $giftCard = GiftCard::factory()->create(['initial_balance' => 10.00, 'current_balance' => 0.00]);
    $order = Order::factory()->confirmed()->unpaid()->create([
        'gift_card_id' => $giftCard->id,
        'gift_card_amount' => 50.00,
    ]);
    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -10.00,
    ]);

    resolve(CancelOrder::class)($order);

    expect($giftCard->refresh()->current_balance->dollars())->toBe(10.00)
        ->and(GiftCardTransaction::query()->where('order_id', $order->id)->where('type', GiftCardTransactionType::Refund)->count())->toBe(1);
});
