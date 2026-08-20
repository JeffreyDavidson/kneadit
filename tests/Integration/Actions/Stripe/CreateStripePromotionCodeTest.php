<?php

use App\Actions\Stripe\CreateStripePromotionCode;
use JMac\Testing\Double;
use JMac\Testing\DoubleInterface;
use JMac\Testing\Matching\Argument;
use Stripe\Service\CouponService;
use Stripe\Service\PromotionCodeService;
use Stripe\StripeClient;

beforeEach(fn () => setUpCentralTest());

class FakePromotionStripeClient extends StripeClient
{
    public function __construct(
        public CouponService $coupons,
        public PromotionCodeService $promotionCodes,
    ) {}
}

function bindMockedStripe(callable $couponsExpectations, callable $promoExpectations): void
{
    $coupons = Double::for(CouponService::class);
    $promotionCodes = Double::for(PromotionCodeService::class);
    $couponsExpectations($coupons);
    $promoExpectations($promotionCodes);

    app()->bind(StripeClient::class, fn (): StripeClient => new FakePromotionStripeClient($coupons, $promotionCodes));
}

test('creates a percent-off once coupon and a promotion code', function () {
    bindMockedStripe(
        function (CouponService&DoubleInterface $m): void {
            $m->expects('create')
                ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload) && ($payload['percent_off'] ?? null) === 100
                    && ($payload['duration'] ?? null) === 'once'
                    && ($payload['max_redemptions'] ?? null) === 1
                    && data_get($payload, 'metadata.tenant_id') === 'vip-baker'))
                ->returns((object) ['id' => 'coupon_abc']);
        },
        function (PromotionCodeService&DoubleInterface $m): void {
            $m->expects('create')
                ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload) && ($payload['coupon'] ?? null) === 'coupon_abc'
                    && ($payload['code'] ?? null) === 'VIP-JANE'))
                ->returns((object) ['id' => 'promo_xyz', 'code' => 'VIP-JANE']);
        },
    );

    $result = resolve(CreateStripePromotionCode::class)(
        percentOff: 100,
        code: 'VIP-JANE',
        tenantId: 'vip-baker',
    );

    expect($result->code)->toBe('VIP-JANE')
        ->and($result->promotionCodeId)->toBe('promo_xyz')
        ->and($result->couponId)->toBe('coupon_abc');
});

test('creates a repeating coupon with duration_in_months', function () {
    bindMockedStripe(
        function (CouponService&DoubleInterface $m): void {
            $m->expects('create')
                ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload) && ($payload['duration'] ?? null) === 'repeating'
                    && ($payload['duration_in_months'] ?? null) === 3))
                ->returns((object) ['id' => 'coupon_rep']);
        },
        function (PromotionCodeService&DoubleInterface $m): void {
            $m->expects('create')->returns((object) ['id' => 'promo_rep', 'code' => 'THREE-MONTHS']);
        },
    );

    resolve(CreateStripePromotionCode::class)(
        percentOff: 50,
        duration: 'repeating',
        durationInMonths: 3,
        code: 'THREE-MONTHS',
    );
});

test('creates an amount-off coupon with currency', function () {
    bindMockedStripe(
        function (CouponService&DoubleInterface $m): void {
            $m->expects('create')
                ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload) && ($payload['amount_off'] ?? null) === 2500
                    && ($payload['currency'] ?? null) === 'usd'
                    && ! isset($payload['percent_off'])))
                ->returns((object) ['id' => 'coupon_amt']);
        },
        function (PromotionCodeService&DoubleInterface $m): void {
            $m->expects('create')->returns((object) ['id' => 'p', 'code' => 'X']);
        },
    );

    resolve(CreateStripePromotionCode::class)(
        amountOffCents: 2500,
        code: 'X',
    );
});

test('rejects when neither percent nor amount is supplied', function () {
    resolve(CreateStripePromotionCode::class)();
})->throws(InvalidArgumentException::class, 'Either percentOff or amountOffCents is required');

test('rejects when both percent and amount are supplied', function () {
    resolve(CreateStripePromotionCode::class)(percentOff: 10, amountOffCents: 500);
})->throws(InvalidArgumentException::class, 'percentOff OR amountOffCents');

test('rejects repeating duration without duration_in_months', function () {
    resolve(CreateStripePromotionCode::class)(percentOff: 50, duration: 'repeating');
})->throws(InvalidArgumentException::class, 'durationInMonths is required');
