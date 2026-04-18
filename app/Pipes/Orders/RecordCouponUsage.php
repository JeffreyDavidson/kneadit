<?php

namespace App\Pipes\Orders;

use App\Actions\Financial\ApplyCoupon;
use App\Enums\Financial\CouponTransactionType;
use App\Models\Financial\Coupon;
use Closure;

class RecordCouponUsage
{
    public function __construct(
        private ApplyCoupon $applyCoupon,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->couponId || $payload->discountAmount <= 0) {
            return $next($payload);
        }

        assert($payload->order !== null, 'Order must be persisted before RecordCouponUsage');

        $coupon = Coupon::query()->find($payload->couponId);

        if ($coupon) {
            ($this->applyCoupon)($coupon);

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
