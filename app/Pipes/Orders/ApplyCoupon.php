<?php

namespace App\Pipes\Orders;

use App\Models\Financial\Coupon;
use App\Services\Coupon\CouponService;
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
            $payload->discountAmount = $this->couponService->calculateDiscount($coupon, $payload->subtotal);
            $payload->couponId = $coupon->id;
            $payload->total = max(0, $payload->total - $payload->discountAmount);
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
