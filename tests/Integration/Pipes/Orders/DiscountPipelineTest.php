<?php

use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Enums\Orders\DeliveryType;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
    test()->product = Product::factory()->create(['price' => 20.00]);
});

function createOrderWith(array $overrides = []): ?Order
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
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 5.00]);

    $order = createOrderWith(['coupon_code' => $coupon->code]);

    expect($order)
        ->not->toBeNull()
        ->coupon_id->toBe($coupon->id)
        ->and($order->discount_amount->dollars())->toBe(5.00)
        ->and($order->total->dollars())->toBe(35.00); // 2 * $20 - $5

    expect(CouponTransaction::query()->where('order_id', $order->id)->count())->toBe(1);

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction)
        ->coupon_id->toBe($coupon->id)
        ->type->toBe(CouponTransactionType::Usage)
        ->and($transaction->amount->dollars())->toBe(5.00)
        ->and($coupon->refresh()->used_count)->toBe(1);
});

test('a coupon that beats the sitewide sale replaces it and is the only discount recorded', function () {
    settings([
        'sitewide_sale_enabled' => '1',
        'sitewide_sale_percent' => '10',
    ]);

    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 5.00]);

    $order = createOrderWith(['coupon_code' => $coupon->code]);

    expect($order)
        ->not->toBeNull()
        ->coupon_id->toBe($coupon->id)
        ->discount_amount->dollars()->toBe(5.00)
        ->and($order->total->dollars())->toBe(35.00)
        ->and($coupon->refresh()->used_count)->toBe(1);

    $transaction = CouponTransaction::query()
        ->where('order_id', $order->id)
        ->sole();

    expect($transaction->amount->dollars())->toBe(5.00);
});

test('a sitewide sale that matches or beats the coupon replaces it and the coupon is not used', function (string $salePercent, float $couponDollars, float $discount) {
    settings([
        'sitewide_sale_enabled' => '1',
        'sitewide_sale_percent' => $salePercent,
    ]);

    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => $couponDollars]);

    $order = createOrderWith(['coupon_code' => $coupon->code]);

    expect($order)
        ->not->toBeNull()
        ->coupon_id->toBeNull()
        ->discount_amount->dollars()->toBe($discount)
        ->and($order->total->dollars())->toBe(40.00 - $discount)
        ->and($coupon->refresh()->used_count)->toBe(0)
        ->and(CouponTransaction::query()->count())->toBe(0);
})->with([
    'sale larger than the coupon' => ['20', 5.00, 8.00],
    'sale equal to the coupon' => ['10', 4.00, 4.00],
]);

test('a coupon is not consumed when the sitewide sale is the better discount on a gift card order', function () {
    settings([
        'sitewide_sale_enabled' => '1',
        'sitewide_sale_percent' => '50',
    ]);

    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 5.00]);
    $giftCard = GiftCard::factory()->withBalance(100.00)->create();

    $order = createOrderWith([
        'coupon_code' => $coupon->code,
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    expect($order->discount_amount->dollars())->toBe(20.00)
        ->and($order->gift_card_amount->dollars())->toBe(20.00)
        ->and($order->total->dollars())->toBe(0.00)
        ->and($coupon->refresh()->used_count)->toBe(0)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(80.00);
});

test('discounts never exceed the subtotal and delivery, and the tip is still owed', function () {
    settings([
        'customer_referral_program_enabled' => true,
        'customer_referral_discount_dollars' => 10,
    ]);
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 40.00]);

    $order = createOrderWith([
        'coupon_code' => $coupon->code,
        'tip_amount' => 3.00,
    ]);

    // $40 subtotal: a $40 coupon plus a $10 referral would be $50 off.
    expect($order->discount_amount->dollars())->toBe(40.00)
        ->and($order->original_discount_amount->dollars())->toBe(40.00)
        ->and($order->total->dollars())->toBe(3.00);
});

test('the gift card pays for the items only, never the tip', function () {
    $giftCard = GiftCard::factory()->withBalance(100.00)->create();

    $order = createOrderWith([
        'items' => [['product_id' => test()->product->id, 'quantity' => 1]],
        'tip_amount' => 5.00,
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    expect($order->gift_card_amount->dollars())->toBe(20.00)
        ->and($order->total->dollars())->toBe(5.00)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(80.00)
        ->and(GiftCardTransaction::query()->where('order_id', $order->id)->sole()->amount->dollars())->toBe(-20.00);
});

test('the gift card draw is reduced by the referral discount', function () {
    settings([
        'customer_referral_program_enabled' => true,
        'customer_referral_discount_dollars' => 10,
    ]);
    Customer::factory()->create(['email' => 'alice@example.com', 'referral_code' => 'ABC12345']);
    Session::put('referral_code', 'ABC12345');
    $giftCard = GiftCard::factory()->withBalance(100.00)->create();

    $order = createOrderWith([
        'items' => [['product_id' => test()->product->id, 'quantity' => 1]],
        'tip_amount' => 5.00,
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    // $20 item - $10 referral = $10 left for the card, then the $5 tip.
    expect($order->discount_amount->dollars())->toBe(10.00)
        ->and($order->gift_card_amount->dollars())->toBe(10.00)
        ->and($order->total->dollars())->toBe(5.00)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(90.00);
});

test('the gift card draw is reduced when a loyalty tier waives the delivery fee', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(
        orders: makeOrderSettings([
            'deliveryFeeTiers' => [['min_distance' => 0, 'max_distance' => 5, 'fee' => 5.00, 'description' => 'Local']],
            'freeDeliveryMinimum' => '0',
        ]),
        loyalty: makeLoyaltySettings(['tierPerksEnabled' => true, 'tierGoldFreeDelivery' => true]),
    ));
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);
    LoyaltyPoint::factory()->earned(2000)->for($customer)->create();
    $giftCard = GiftCard::factory()->withBalance(100.00)->create();

    $order = createOrderWith([
        'delivery_type' => DeliveryType::Delivery->value,
        'delivery_tier' => '0',
        'delivery_address' => '1 Main St',
        'items' => [['product_id' => test()->product->id, 'quantity' => 1]],
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    // Delivery was $5 until the Gold perk waived it: the card pays the $20 item only.
    expect($order->delivery_fee->dollars())->toBe(0.00)
        ->and($order->gift_card_amount->dollars())->toBe(20.00)
        ->and($order->total->dollars())->toBe(0.00)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(80.00);
});

test('order with gift card stores gift_card_id and gift_card_amount', function () {
    $giftCard = GiftCard::factory()->withBalance(50.00)->create();

    $order = createOrderWith([
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    expect($order)
        ->not->toBeNull()
        ->gift_card_id->toBe($giftCard->id)
        ->and($order->gift_card_amount->dollars())->toBe(40.00); // min($50 balance, $40 total)
    expect($order->total->dollars())->toBe(0.00);

    expect($giftCard->refresh()->current_balance->dollars())->toBe(10.00)
        ->and(GiftCardTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', GiftCardTransactionType::Redemption)->count())->toBe(1);
});

test('order with both coupon and gift card applies coupon first then gift card', function () {
    $coupon = Coupon::factory()->fixed()->create(['fixed_amount' => 10.00]);
    $giftCard = GiftCard::factory()->withBalance(50.00)->create();

    $order = createOrderWith([
        'coupon_code' => $coupon->code,
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    // Subtotal: 2 * $20 = $40
    // After coupon: $40 - $10 = $30
    // After gift card: $30 - $30 = $0 (gift card covers remaining)
    expect($order->discount_amount->dollars())->toBe(10.00);
    expect($order->gift_card_amount->dollars())->toBe(30.00)
        ->and($order->total->dollars())->toBe(0.00)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(20.00)
        ->and($coupon->refresh()->used_count)->toBe(1)
        ->and(CouponTransaction::query()->where('order_id', $order->id)->count())->toBe(1)
        ->and(GiftCardTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', GiftCardTransactionType::Redemption)->count())->toBe(1);
});

test('gift card with insufficient balance applies partial amount', function () {
    $giftCard = GiftCard::factory()->withBalance(15.00)->create();

    $order = createOrderWith([
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    // Subtotal: $40, gift card: $15, remaining: $25
    expect($order->gift_card_amount->dollars())->toBe(15.00);
    expect($order->total->dollars())->toBe(25.00)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(0.00);
});

test('order without discounts has zero discount and gift card amounts', function () {
    $order = createOrderWith();

    expect($order)
        ->coupon_id->toBeNull()
        ->gift_card_id->toBeNull()
        ->and($order->discount_amount->dollars())->toBe(0.00)
        ->and($order->gift_card_amount->dollars())->toBe(0.00)
        ->and($order->total->dollars())->toBe(40.00)
        ->and(CouponTransaction::query()->count())->toBe(0);
});

test('expired gift card is not applied', function () {
    $giftCard = GiftCard::factory()->expired()->create(['initial_balance' => 50.00, 'current_balance' => 50.00]);

    $order = createOrderWith([
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    expect($order)
        ->gift_card_id->toBeNull()
        ->and($order->gift_card_amount->dollars())->toBe(0.00)
        ->and($order->total->dollars())->toBe(40.00)
        ->and($giftCard->refresh()->current_balance->dollars())->toBe(50.00);
});

test('depleted gift card is not applied', function () {
    $giftCard = GiftCard::factory()->depleted()->create(['initial_balance' => 50.00]);

    $order = createOrderWith([
        'gift_card_id' => $giftCard->id,
        'gift_card_code' => $giftCard->code,
    ]);

    expect($order)
        ->gift_card_id->toBeNull()
        ->and($order->gift_card_amount->dollars())->toBe(0.00)
        ->and($order->total->dollars())->toBe(40.00);
});

test('percentage coupon creates transaction with calculated amount', function () {
    $coupon = Coupon::factory()->percentage()->create(['percentage' => 25.00]); // 25%

    $order = createOrderWith(['coupon_code' => $coupon->code]);

    // 25% of $40 = $10
    expect($order->discount_amount->dollars())->toBe(10.00);
    expect($order->total->dollars())->toBe(30.00);

    $transaction = CouponTransaction::query()->where('order_id', $order->id)->first();
    expect($transaction->amount->dollars())->toBe(10.00);
});
