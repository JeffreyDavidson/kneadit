<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Orders\DeliveryType;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Inventory\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    test()->product = Product::factory()->create(['price' => 20.00]);
});

function createOrderWith(array $overrides = []): ?App\Models\Orders\Order
{
    return resolve(CreateOrder::class)(
        CreateOrderData::fromArray(array_merge([
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'delivery_date' => now()->addDays(5)->toDateString(),
            'delivery_type' => DeliveryType::Pickup->value,
            'items' => [
                ['product_id' => test()->product->id, 'quantity' => 2],
            ],
        ], $overrides))
    );
}

test('order with coupon stores discount_amount and creates coupon transaction', function () {
    $coupon = Coupon::factory()->fixed()->create(['value' => 5.00]);

    $order = createOrderWith(['coupon_id' => $coupon->id]);

    expect($order)
        ->not->toBeNull()
        ->coupon_id->toBe($coupon->id)
        ->discount_amount->toBe('5.00')
        ->total->toBe('35.00'); // 2 * $20 - $5

    expect(CouponTransaction::query()->where('order_id', $order->id)->count())->toBe(1);

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction)
        ->coupon_id->toBe($coupon->id)
        ->type->toBe(CouponTransactionType::Usage);

    expect($transaction->amount->dollars())->toBe(5.00);

    expect($coupon->refresh()->used_count)->toBe(1);
});

test('order with gift card stores gift_card_id and gift_card_amount', function () {
    $giftCard = GiftCard::factory()->withBalance(50.00)->create();

    $order = createOrderWith(['gift_card_id' => $giftCard->id]);

    expect($order)
        ->not->toBeNull()
        ->gift_card_id->toBe($giftCard->id)
        ->gift_card_amount->toBe('40.00') // min($50 balance, $40 total)
        ->total->toBe('0.00');

    expect($giftCard->refresh()->current_balance->dollars())->toBe(10.00);

    expect(GiftCardTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', GiftCardTransactionType::Redemption)
        ->count())->toBe(1);
});

test('order with both coupon and gift card applies coupon first then gift card', function () {
    $coupon = Coupon::factory()->fixed()->create(['value' => 10.00]);
    $giftCard = GiftCard::factory()->withBalance(50.00)->create();

    $order = createOrderWith([
        'coupon_id' => $coupon->id,
        'gift_card_id' => $giftCard->id,
    ]);

    // Subtotal: 2 * $20 = $40
    // After coupon: $40 - $10 = $30
    // After gift card: $30 - $30 = $0 (gift card covers remaining)
    expect($order)
        ->discount_amount->toBe('10.00')
        ->gift_card_amount->toBe('30.00')
        ->total->toBe('0.00');

    expect($giftCard->refresh()->current_balance->dollars())->toBe(20.00);
    expect($coupon->refresh()->used_count)->toBe(1);

    expect(CouponTransaction::query()->where('order_id', $order->id)->count())->toBe(1);
    expect(GiftCardTransaction::query()
        ->where('order_id', $order->id)
        ->where('type', GiftCardTransactionType::Redemption)
        ->count())->toBe(1);
});

test('gift card with insufficient balance applies partial amount', function () {
    $giftCard = GiftCard::factory()->withBalance(15.00)->create();

    $order = createOrderWith(['gift_card_id' => $giftCard->id]);

    // Subtotal: $40, gift card: $15, remaining: $25
    expect($order)
        ->gift_card_amount->toBe('15.00')
        ->total->toBe('25.00');

    expect($giftCard->refresh()->current_balance->dollars())->toBe(0.00);
});

test('order without discounts has zero discount and gift card amounts', function () {
    $order = createOrderWith();

    expect($order)
        ->discount_amount->toBe('0.00')
        ->gift_card_amount->toBe('0.00')
        ->coupon_id->toBeNull()
        ->gift_card_id->toBeNull()
        ->total->toBe('40.00');

    expect(CouponTransaction::query()->count())->toBe(0);
});

test('expired gift card is not applied', function () {
    $giftCard = GiftCard::factory()->expired()->create(['initial_balance' => 50.00, 'current_balance' => 50.00]);

    $order = createOrderWith(['gift_card_id' => $giftCard->id]);

    expect($order)
        ->gift_card_id->toBeNull()
        ->gift_card_amount->toBe('0.00')
        ->total->toBe('40.00');

    expect($giftCard->refresh()->current_balance->dollars())->toBe(50.00);
});

test('depleted gift card is not applied', function () {
    $giftCard = GiftCard::factory()->depleted()->create(['initial_balance' => 50.00]);

    $order = createOrderWith(['gift_card_id' => $giftCard->id]);

    expect($order)
        ->gift_card_id->toBeNull()
        ->gift_card_amount->toBe('0.00')
        ->total->toBe('40.00');
});

test('percentage coupon creates transaction with calculated amount', function () {
    $coupon = Coupon::factory()->percentage()->create(['value' => 25.00]); // 25%

    $order = createOrderWith(['coupon_id' => $coupon->id]);

    // 25% of $40 = $10
    expect($order)
        ->discount_amount->toBe('10.00')
        ->total->toBe('30.00');

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction->amount->dollars())->toBe(10.00);
});
