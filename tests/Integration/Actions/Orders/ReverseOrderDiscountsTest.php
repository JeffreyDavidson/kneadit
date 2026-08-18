<?php

use App\Actions\Orders\ReverseOrderDiscounts;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
});

test('restores coupon used_count and creates reversal transaction', function () {
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 5.00, 'used_count' => 1]);
    $order = Order::factory()
        ->recycle(test()->user)
        ->create([
            'coupon_id' => $coupon->id,
            'discount_amount' => 5.00,
        ]);

    CouponTransaction::factory()->create([
        'coupon_id' => $coupon->id,
        'order_id' => $order->id,
        'amount' => 5.00,
        'type' => CouponTransactionType::Usage,
    ]);

    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');

    expect($coupon->refresh()->used_count)->toBe(0)
        ->and(CouponTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', CouponTransactionType::Reversal)->count())->toBe(1);
});

test('restores gift card balance and creates refund transaction', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);
    $order = Order::factory()
        ->recycle(test()->user)
        ->create([
            'gift_card_id' => $giftCard->id,
            'gift_card_amount' => 20.00,
        ]);

    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -20.00,
    ]);

    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');

    expect($giftCard->refresh()->current_balance->dollars())->toBe(50.00)
        ->and(GiftCardTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', GiftCardTransactionType::Refund)->count())->toBe(1);
});

test('reverses both coupon and gift card on same order', function () {
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 10.00, 'used_count' => 1]);
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 20.00,
    ]);
    $order = Order::factory()
        ->recycle(test()->user)
        ->create([
            'coupon_id' => $coupon->id,
            'discount_amount' => 10.00,
            'gift_card_id' => $giftCard->id,
            'gift_card_amount' => 30.00,
        ]);

    CouponTransaction::factory()->create([
        'coupon_id' => $coupon->id,
        'order_id' => $order->id,
        'amount' => 10.00,
        'type' => CouponTransactionType::Usage,
    ]);
    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -30.00,
    ]);

    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');

    expect($coupon->refresh()->used_count)->toBe(0)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(50.00);
});

test('does nothing for order without discounts', function () {
    $order = Order::factory()
        ->recycle(test()->user)
        ->create();

    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');

    expect(CouponTransaction::query()->count())->toBe(0)
        ->and(GiftCardTransaction::query()->count())->toBe(0);
});

test('is idempotent when called twice', function () {
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 5.00, 'used_count' => 1]);
    $order = Order::factory()
        ->recycle(test()->user)
        ->create([
            'coupon_id' => $coupon->id,
            'discount_amount' => 5.00,
        ]);

    CouponTransaction::factory()->create([
        'coupon_id' => $coupon->id,
        'order_id' => $order->id,
        'amount' => 5.00,
        'type' => CouponTransactionType::Usage,
    ]);

    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');
    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');

    expect($coupon->refresh()->used_count)->toBe(0)
        ->and(CouponTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', CouponTransactionType::Reversal)->count())->toBe(1);
});

test('gift card reversal is idempotent when called twice', function () {
    $giftCard = GiftCard::factory()->create([
        'initial_balance' => 50.00,
        'current_balance' => 30.00,
    ]);
    $order = Order::factory()
        ->recycle(test()->user)
        ->create([
            'gift_card_id' => $giftCard->id,
            'gift_card_amount' => 20.00,
        ]);

    GiftCardTransaction::factory()->redemption()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
        'amount' => -20.00,
    ]);

    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');
    resolve(ReverseOrderDiscounts::class)($order, 'Order cancelled');

    expect($giftCard->refresh()->current_balance->dollars())->toBe(50.00)
        ->and(GiftCardTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', GiftCardTransactionType::Refund)->count())->toBe(1);
});
