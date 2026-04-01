<?php

namespace App\Pipes\Orders;

use App\Models\Financial\Coupon;
use App\Services\Coupon\CouponService;
use Closure;

class ApplyCouponUsage
{
    public function __construct(
        private CouponService $couponService,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if ($payload->couponId) {
            $coupon = Coupon::query()->find($payload->couponId);
            if ($coupon) {
                $this->couponService->apply($coupon);
            }
        }

        return $next($payload);
    }
}
