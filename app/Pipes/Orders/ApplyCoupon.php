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
        private CouponService $couponService,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $coupon = $this->resolveCoupon($payload);

        if (! $coupon) {
            return $next($payload);
        }

        if ($this->couponService->isValid($coupon)) {
            $couponDiscount = Money::fromDollars($this->couponService->calculateDiscount($coupon, $payload->subtotal->dollars()));
            $payload->couponDiscount = $couponDiscount;
            $payload->recalculateDiscountAmount();
            $payload->couponId = $coupon->id;
            $payload->recalculateTotal();
        }

        return $next($payload);
    }

    private function resolveCoupon(OrderPipelineData $payload): ?Coupon
    {
        if ($payload->data->couponId) {
            return Coupon::query()->lockForUpdate()->find($payload->data->couponId);
        }

        if ($payload->data->couponCode) {
            return Coupon::query()
                ->where('code', Str::upper(trim($payload->data->couponCode)))
                ->lockForUpdate()
                ->first();
        }

        return null;
    }
}
