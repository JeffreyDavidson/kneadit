<?php

namespace App\Pipes\Orders;

use App\Models\Coupon;
use App\Services\Coupon\CouponService;
use Closure;

class ApplyCoupon
{
    public function __construct(
        private CouponService $couponService,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->data->couponId) {
            return $next($payload);
        }

        $coupon = Coupon::query()->lockForUpdate()->find($payload->data->couponId);

        if ($coupon && $this->couponService->isValid($coupon)) {
            $payload->discountAmount = $this->couponService->calculateDiscount($coupon, $payload->subtotal);
            $payload->couponId = $coupon->id;
            $payload->total = max(0, $payload->total - $payload->discountAmount);
        }

        return $next($payload);
    }
}
