<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Orders\OrderStatus;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('order number is auto-generated on create', function () {
    $order = Order::factory()->create(['order_number' => null]);

    expect($order->order_number)->toStartWith('ORD-');
});

test('order number is not overwritten if already set', function () {
    $order = Order::factory()->create(['order_number' => 'CUSTOM-001']);

    expect($order->order_number)->toBe('CUSTOM-001');
});

test('deleting a pending order restores the gift card balance it drew', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);
    $order = Order::factory()->pending()->unpaid()->create([
        'gift_card_id' => $giftCard->id,
        'gift_card_amount' => 20.00,
    ]);
    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -20.00,
    ]);

    $order->delete();

    expect($giftCard->refresh()->current_balance->dollars())->toBe(50.00);
});

test('deleting a pending order releases the coupon use it counted', function () {
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 5.00, 'used_count' => 1]);
    $order = Order::factory()->pending()->unpaid()->create([
        'coupon_id' => $coupon->id,
        'discount_amount' => 5.00,
    ]);
    CouponTransaction::factory()->create([
        'coupon_id' => $coupon->id,
        'order_id' => $order->id,
        'amount' => 5.00,
        'type' => CouponTransactionType::Usage,
    ]);

    $order->delete();

    expect($coupon->refresh()->used_count)->toBe(0);
});

test('deleting an already cancelled order does not reverse its discounts twice', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);
    $order = Order::factory()->pending()->unpaid()->create([
        'gift_card_id' => $giftCard->id,
        'gift_card_amount' => 20.00,
    ]);
    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -20.00,
    ]);
    resolve(TransitionOrderStatus::class)($order, OrderStatus::Cancelled);

    $order->refresh()->delete();

    expect($giftCard->refresh()->current_balance->dollars())->toBe(50.00);
});
