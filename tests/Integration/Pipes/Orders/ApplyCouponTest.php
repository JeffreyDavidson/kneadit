<?php

use App\DataTransferObjects\Orders\CreateOrderData;
use App\Enums\Orders\DeliveryType;
use App\Models\Financial\Coupon;
use App\Pipes\Orders\ApplyCoupon;
use App\Pipes\Orders\OrderPipelineData;
use App\Services\Coupon\CouponService;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function makeCouponPayload(?string $couponCode): OrderPipelineData
{
    $payload = new OrderPipelineData(new CreateOrderData(
        customerName: 'Jane Doe',
        customerEmail: 'jane@example.com',
        deliveryDate: now()->addDay()->format('Y-m-d'),
        deliveryType: DeliveryType::Pickup->value,
        items: [['product_id' => 1, 'quantity' => 1]],
        couponCode: $couponCode,
    ));
    $payload->subtotal = Money::fromDollars(30.0);
    $payload->recalculateTotal();

    return $payload;
}

test('resolves the coupon by its code, ignoring case and surrounding whitespace', function (string $submittedCode) {
    $coupon = Coupon::factory()->fixed()->create(['code' => 'BACK-ABC12', 'fixed_amount' => 5.00]);
    $payload = makeCouponPayload($submittedCode);

    $result = new ApplyCoupon(resolve(CouponService::class))->handle($payload, fn ($p) => $p);

    expect($result->couponId)->toBe($coupon->id)
        ->and($result->couponDiscount->dollars())->toBe(5.0)
        ->and($result->total->dollars())->toBe(25.0);
})->with([
    'exact' => 'BACK-ABC12',
    'lowercase' => 'back-abc12',
    'padded' => '  Back-Abc12 ',
]);

test('applies no coupon when the code is missing or unknown', function (?string $submittedCode) {
    Coupon::factory()->fixed()->create(['code' => 'BACK-ABC12', 'fixed_amount' => 5.00]);
    $payload = makeCouponPayload($submittedCode);

    $result = new ApplyCoupon(resolve(CouponService::class))->handle($payload, fn ($p) => $p);

    expect($result->couponId)->toBeNull()
        ->and($result->couponDiscount->dollars())->toBe(0.0)
        ->and($result->total->dollars())->toBe(30.0);
})->with([
    'no code' => null,
    'unknown code' => 'BACK-ZZZZZ',
]);
