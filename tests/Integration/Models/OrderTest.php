<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\Review;
use App\Models\Engagement\SurveyResponse;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->user = User::factory()->owner()->create();
    $this->customer = Customer::factory()->create();
});

test('order has customer relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    expect($order->customer)->toBeInstanceOf(Customer::class)
        ->and($order->customer->id)->toBe($this->customer->id);
});

test('order has items relationship', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    OrderItem::factory()->recycle($order, $product)->create(['quantity' => 2, 'unit_price' => 5.00]);

    expect($order->refresh()->orderItems)->toHaveCount(1)
        ->and($order->orderItems->first()->quantity)->toBe(2);
});

test('order has messages relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    OrderMessage::factory()->fromBaker()->recycle($order)->create();

    expect($order->messages)->toHaveCount(1);
});

test('order number is auto generated', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    expect($order->order_number)->toStartWith('ORD-');
});

test('order total is cast to decimal', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'subtotal' => 25.50,
        'delivery_fee' => 5.00,
        'discount_amount' => 2.50,
        'total' => 28.00,
    ]);

    expect($order->refresh()->total)->toBe('28.00')
        ->and($order->delivery_fee)->toBe('5.00')
        ->and($order->discount_amount)->toBe('2.50');
});

test('order status transitions', function (OrderStatus $status) {
    Mail::fake();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    $order->update(['status' => $status]);

    expect($order->fresh()->status)->toBe($status);
})->with([
    'confirmed' => [OrderStatus::Confirmed],
    'baking' => [OrderStatus::Baking],
    'ready' => [OrderStatus::Ready],
    'delivered' => [OrderStatus::Delivered],
]);

test('order can be cancelled', function () {
    Mail::fake();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();
    $order->update(['status' => OrderStatus::Cancelled]);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('order belongs to user', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    expect($order->user)->toBeInstanceOf(User::class);
});

test('order item total price attribute', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'subtotal' => 15.00,
        'total' => 15.00,
    ]);

    $item = OrderItem::factory()->recycle($order, $product)->create([
        'quantity' => 3,
        'unit_price' => 5.00,
    ]);

    expect($item->total_price)->toBe(15.00);
});

test('order has loyalty points relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    LoyaltyPoint::factory()->create([
        'customer_id' => $this->customer->id,
        'order_id' => $order->id,
    ]);

    expect($order->loyaltyPoints)->toHaveCount(1);
});

test('order has reviews relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    Review::factory()->create(['order_id' => $order->id]);

    expect($order->reviews)->toHaveCount(1);
});

test('order has survey responses relationship', function () {
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create();

    SurveyResponse::factory()->create(['order_id' => $order->id]);

    expect($order->surveyResponses)->toHaveCount(1);
});

test('order has coupon transactions relationship', function () {
    $coupon = Coupon::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'coupon_id' => $coupon->id,
    ]);

    CouponTransaction::factory()->create([
        'coupon_id' => $coupon->id,
        'order_id' => $order->id,
    ]);

    expect($order->couponTransactions)->toHaveCount(1);
});

test('order has gift card transactions relationship', function () {
    $giftCard = GiftCard::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'gift_card_id' => $giftCard->id,
    ]);

    GiftCardTransaction::factory()->create([
        'gift_card_id' => $giftCard->id,
        'order_id' => $order->id,
    ]);

    expect($order->giftCardTransactions)->toHaveCount(1);
});

test('order belongs to coupon', function () {
    $coupon = Coupon::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'coupon_id' => $coupon->id,
    ]);

    expect($order->coupon)->toBeInstanceOf(Coupon::class)
        ->and($order->coupon->id)->toBe($coupon->id);
});

test('order belongs to gift card', function () {
    $giftCard = GiftCard::factory()->create();
    $order = Order::factory()->for($this->customer)->recycle($this->user)->create([
        'gift_card_id' => $giftCard->id,
    ]);

    expect($order->giftCard)->toBeInstanceOf(GiftCard::class)
        ->and($order->giftCard->id)->toBe($giftCard->id);
});
