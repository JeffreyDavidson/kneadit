<?php

use App\Actions\Stripe\CreateStripePromotionCode;
use App\DataTransferObjects\Stripe\StripePromotionCodeResult;

use function Pest\Laravel\artisan;

beforeEach(fn () => setUpCentralTest());

test('invokes the action with parsed options and prints the resulting code', function () {
    $action = Mockery::mock(CreateStripePromotionCode::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->andReturn(new StripePromotionCodeResult(
            promotionCodeId: 'promo_123',
            code: 'VIP-JANE-2026',
            couponId: 'coupon_456',
        ));

    app()->instance(CreateStripePromotionCode::class, $action);

    artisan('platform:create-promo', [
        '--tenant' => 'vip-baker',
        '--percent' => '100',
        '--duration' => 'repeating',
        '--months' => '1',
        '--code' => 'VIP-JANE-2026',
        '--expires-in' => '30',
        '--name' => 'VIP onboarding',
    ])
        ->expectsOutputToContain('VIP-JANE-2026')
        ->expectsOutputToContain('coupon_456')
        ->expectsOutputToContain('promo_123')
        ->assertSuccessful();
});

test('reports invalid-argument errors with exit code 1', function () {
    $action = Mockery::mock(CreateStripePromotionCode::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->andThrow(new InvalidArgumentException('Either percentOff or amountOffCents is required.'));

    app()->instance(CreateStripePromotionCode::class, $action);

    artisan('platform:create-promo')
        ->expectsOutputToContain('Either percentOff or amountOffCents is required')
        ->assertFailed();
});
