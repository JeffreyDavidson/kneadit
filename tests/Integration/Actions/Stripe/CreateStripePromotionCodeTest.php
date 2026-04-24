<?php

use App\Actions\Stripe\CreateStripePromotionCode;
use Mockery\MockInterface;
use Stripe\Service\CouponService;
use Stripe\Service\PromotionCodeService;
use Stripe\StripeClient;

beforeEach(fn () => setUpCentralTest());

function bindMockedStripe(callable $couponsExpectations, callable $promoExpectations): void
{
    $coupons = mock(CouponService::class, $couponsExpectations);
    $promotionCodes = mock(PromotionCodeService::class, $promoExpectations);

    app()->bind(StripeClient::class, function () use ($coupons, $promotionCodes): StripeClient {
        $client = mock(StripeClient::class);
        $client->coupons = $coupons;
        $client->promotionCodes = $promotionCodes;

        return $client;
    });
}

test('creates a percent-off once coupon and a promotion code', function () {
    bindMockedStripe(
        function (MockInterface $m): void {
            $m->shouldReceive('create')
                ->once()
                ->with(Mockery::on(fn (array $payload): bool => ($payload['percent_off'] ?? null) === 100
                    && ($payload['duration'] ?? null) === 'once'
                    && ($payload['max_redemptions'] ?? null) === 1
                    && ($payload['metadata']['tenant_id'] ?? null) === 'vip-baker'))
                ->andReturn((object) ['id' => 'coupon_abc']);
        },
        function (MockInterface $m): void {
            $m->shouldReceive('create')
                ->once()
                ->with(Mockery::on(fn (array $payload): bool => ($payload['coupon'] ?? null) === 'coupon_abc'
                    && ($payload['code'] ?? null) === 'VIP-JANE'))
                ->andReturn((object) ['id' => 'promo_xyz', 'code' => 'VIP-JANE']);
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
        function (MockInterface $m): void {
            $m->shouldReceive('create')
                ->once()
                ->with(Mockery::on(fn (array $payload): bool => ($payload['duration'] ?? null) === 'repeating'
                    && ($payload['duration_in_months'] ?? null) === 3))
                ->andReturn((object) ['id' => 'coupon_rep']);
        },
        function (MockInterface $m): void {
            $m->shouldReceive('create')
                ->once()
                ->andReturn((object) ['id' => 'promo_rep', 'code' => 'THREE-MONTHS']);
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
        function (MockInterface $m): void {
            $m->shouldReceive('create')
                ->once()
                ->with(Mockery::on(fn (array $payload): bool => ($payload['amount_off'] ?? null) === 2500
                    && ($payload['currency'] ?? null) === 'usd'
                    && ! isset($payload['percent_off'])))
                ->andReturn((object) ['id' => 'coupon_amt']);
        },
        function (MockInterface $m): void {
            $m->shouldReceive('create')->once()->andReturn((object) ['id' => 'p', 'code' => 'X']);
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
