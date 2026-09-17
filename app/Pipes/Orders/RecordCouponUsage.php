<?php

namespace App\Pipes\Orders;

use App\Actions\Financial\ApplyCoupon;
use App\Enums\Financial\CouponTransactionType;
use App\Models\Financial\Coupon;
use App\Models\Orders\Order;
use Closure;

class RecordCouponUsage
{
    public function __construct(
        private readonly ApplyCoupon $applyCoupon,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->couponId || ! $payload->couponDiscount->isPositive()) {
            return $next($payload);
        }

        assert($payload->order instanceof Order, 'Order must be persisted before RecordCouponUsage');

        $coupon = Coupon::query()->find($payload->couponId);

        if ($coupon) {
            ($this->applyCoupon)($coupon);

            $coupon->transactions()->create([
                'amount' => $payload->couponDiscount,
                'type' => CouponTransactionType::Usage,
                'order_id' => $payload->order->id,
                'notes' => "Applied to order #{$payload->order->order_number}",
                'created_at' => now(),
            ]);
        }

        return $next($payload);
    }
}
