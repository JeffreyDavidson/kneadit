<?php

use App\Actions\Stripe\CreateStripePromotionCode;
use App\DataTransferObjects\Stripe\StripePromotionCodeResult;

use function Pest\Laravel\artisan;

class FakeCreateStripePromotionCode extends CreateStripePromotionCode
{
    public int $invocations = 0;

    /** @param Closure(): StripePromotionCodeResult $callback */
    public function __construct(private readonly Closure $callback) {}

    public function __invoke(
        ?int $percentOff = null,
        ?int $amountOffCents = null,
        string $duration = 'once',
        ?int $durationInMonths = null,
        ?string $code = null,
        int $maxRedemptions = 1,
        ?int $expiresInDays = null,
        ?string $tenantId = null,
        ?string $name = null,
    ): StripePromotionCodeResult {
        $this->invocations++;

        return ($this->callback)();
    }
}

beforeEach(fn () => setUpCentralTest());

test('invokes the action with parsed options and prints the resulting code', function () {
    $action = new FakeCreateStripePromotionCode(
        fn () => new StripePromotionCodeResult(
            promotionCodeId: 'promo_123',
            code: 'VIP-JANE-2026',
            couponId: 'coupon_456',
        ),
    );

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

    expect($action->invocations)->toBe(1);
});

test('reports invalid-argument errors with exit code 1', function () {
    $action = new FakeCreateStripePromotionCode(
        fn () => throw new InvalidArgumentException('Either percentOff or amountOffCents is required.'),
    );

    app()->instance(CreateStripePromotionCode::class, $action);

    artisan('platform:create-promo')
        ->expectsOutputToContain('Either percentOff or amountOffCents is required')
        ->assertFailed();

    expect($action->invocations)->toBe(1);
});
