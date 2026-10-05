<?php

use App\Filament\Central\Pages\PromoCode;
use App\Models\Platform\PlatformPromoCode;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use JMac\Testing\Double;
use JMac\Testing\Matching\Argument;
use Stripe\Service\CouponService;
use Stripe\Service\PromotionCodeService;
use Stripe\StripeClient;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

final class FakePromoPageStripeClient extends StripeClient
{
    public function __construct(
        public CouponService $coupons,
        public PromotionCodeService $promotionCodes,
    ) {}
}

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('promo code page renders', function () {
    livewire(PromoCode::class)->assertOk();
});

test('history list returns recent codes', function () {
    PlatformPromoCode::query()->create([
        'code' => 'CODE_A',
        'coupon_id' => 'c1',
        'promotion_code_id' => 'p1',
        'percent_off' => 10,
        'duration' => 'once',
        'max_redemptions' => 1,
    ]);
    PlatformPromoCode::query()->create([
        'code' => 'CODE_B',
        'coupon_id' => 'c2',
        'promotion_code_id' => 'p2',
        'percent_off' => 25,
        'duration' => 'once',
        'max_redemptions' => 1,
    ]);

    $page = new PromoCode;
    $codes = $page->getRecentCodes();

    expect($codes)->toHaveCount(2)
        ->and($codes->pluck('code')->all())->toContain('CODE_A', 'CODE_B');
});

test('generate creates a code when the optional fields are left blank', function () {
    $coupons = Double::for(CouponService::class);
    $coupons->expects('create')
        ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload)
            && ($payload['percent_off'] ?? null) === 20
            && ! array_key_exists('name', $payload)
            && ($payload['metadata'] ?? []) === []))
        ->returns((object) ['id' => 'coupon_blank']);

    $promotionCodes = Double::for(PromotionCodeService::class);
    $promotionCodes->expects('create')
        ->with(Argument::satisfies(fn (mixed $payload): bool => is_array($payload)
            && ($payload['coupon'] ?? null) === 'coupon_blank'
            && ! array_key_exists('code', $payload)
            && ! array_key_exists('expires_at', $payload)))
        ->returns((object) ['id' => 'promo_blank', 'code' => 'GENERATED20']);

    app()->bind(StripeClient::class, fn (): StripeClient => new FakePromoPageStripeClient($coupons, $promotionCodes));

    livewire(PromoCode::class)
        ->fillForm([
            'discount_type' => 'percent',
            'discount_value' => 20,
            'duration' => 'once',
            'max_redemptions' => 1,
            'code' => null,
            'name' => null,
            'expires_in_days' => null,
            'tenant_id' => null,
        ])
        ->call('generate')
        ->assertNotified('Promo code created');

    $row = PlatformPromoCode::query()->sole();

    expect($row->code)->toBe('GENERATED20')
        ->and($row->tenant_id)->toBeNull()
        ->and($row->name)->toBeNull()
        ->and($row->expires_at)->toBeNull();
});
