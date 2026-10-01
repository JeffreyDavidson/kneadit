<?php

use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Platform\Tenant;
use App\Services\Stripe\StripeCheckoutService;
use JMac\Testing\Double;
use Stripe\Checkout\Session;
use Stripe\Service\Checkout\SessionService;
use Stripe\Service\CouponService;
use Stripe\StripeClient;

beforeEach(function () {
    setUpCentralTest();
    createTenant(['id' => 'discount-tenant', 'email' => 'discount@test.com']);
    tenancy()->tenant = Tenant::query()->findOrFail('discount-tenant');
    settings(['stripe_connect_id' => 'acct_test']);
});

afterEach(fn () => tenancy()->tenant = null);

final class FakeStripeDiscountClient extends StripeClient
{
    public object $checkout;

    public function __construct(SessionService $sessions, public CouponService $coupons)
    {
        $this->checkout = (object) ['sessions' => $sessions];
    }
}

test('creates a coupon for a gift card even when the order has no discount', function () {
    $order = Order::factory()->unpaid()->create([
        'subtotal' => 40.00,
        'discount_amount' => 0,
        'gift_card_amount' => 10.00,
        'total' => 30.00,
    ]);
    OrderItem::factory()->for($order)->create(['quantity' => 1, 'unit_price' => 40.00]);

    $coupons = Double::for(CouponService::class);
    $coupons->expects('create')->returns((object) ['id' => 'coupon_gift']);
    $sessions = Double::for(SessionService::class);
    $sessions->expects('create')->returns(Session::constructFrom(['id' => 'cs_test_gift', 'url' => 'https://stripe.test/pay']));
    app()->bind(StripeClient::class, fn (): StripeClient => new FakeStripeDiscountClient($sessions, $coupons));

    $session = resolve(StripeCheckoutService::class)->createCheckoutSession($order, 'https://success.test', 'https://cancel.test');

    expect($session?->id)->toBe('cs_test_gift');
});
