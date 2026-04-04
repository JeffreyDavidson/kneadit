<?php

namespace App\Pipes\Orders;

use App\Enums\Financial\CouponTransactionType;
use App\Models\Financial\Coupon;
use App\Services\Coupon\CouponService;
use Closure;

class RecordCouponUsage
{
    public function __construct(
        private CouponService $couponService,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->couponId || $payload->discountAmount <= 0) {
            return $next($payload);
        }

        $coupon = Coupon::query()->find($payload->couponId);

        if ($coupon) {
            $this->couponService->apply($coupon);

            $coupon->transactions()->create([
                'amount' => $payload->discountAmount,
                'type' => CouponTransactionType::Usage,
                'order_id' => $payload->order->id,
                'notes' => "Applied to order #{$payload->order->order_number}",
                'created_at' => now(),
            ]);
        }

        return $next($payload);
    }
}
