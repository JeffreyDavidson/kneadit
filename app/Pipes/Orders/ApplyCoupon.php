<?php

namespace App\Pipes\Orders;

use App\Models\Financial\Coupon;
use App\Services\Coupon\CouponService;
use App\ValueObjects\Money;
use Closure;
use Illuminate\Support\Str;

class ApplyCoupon
{
    public function __construct(
        private readonly CouponService $couponService,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $coupon = $this->resolveCoupon($payload);

        if (! $coupon instanceof Coupon) {
            return $next($payload);
        }

        if (! $this->couponService->isValid($coupon)) {
            return $next($payload);
        }

        $couponDiscount = Money::fromDollars($this->couponService->calculateDiscount($coupon, $payload->subtotal->dollars()));

        // A coupon and the sitewide sale don't stack: the customer gets the larger
        // one, and a coupon that loses is neither applied nor counted as used.
        if (! $couponDiscount->greaterThan($payload->sitewideSaleDiscount)) {
            return $next($payload);
        }

        $payload->sitewideSaleDiscount = Money::zero();
        $payload->couponDiscount = $couponDiscount;
        $payload->couponId = $coupon->id;
        $payload->recalculateDiscountAmount();
        $payload->recalculateTotal();

        return $next($payload);
    }

    private function resolveCoupon(OrderPipelineData $payload): ?Coupon
    {
        if (! $payload->data->couponCode) {
            return null;
        }

        return Coupon::query()
            ->where('code', Str::upper(trim($payload->data->couponCode)))
            ->lockForUpdate()
            ->first();
    }
}
