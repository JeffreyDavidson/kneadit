<?php

namespace App\Pipes\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Closure;

class PersistOrder
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        $payload->order = Order::query()->create([
            'customer_id' => $payload->customer->id,
            'delivery_date' => $payload->data->deliveryDate,
            'delivery_time' => $payload->data->deliveryTime,
            'delivery_type' => $payload->data->deliveryType,
            'delivery_address' => $payload->data->deliveryAddress,
            'subtotal' => $payload->subtotal,
            'delivery_fee' => $payload->deliveryFee,
            'discount_amount' => $payload->discountAmount,
            'coupon_id' => $payload->couponId,
            'total' => $payload->total,
            'notes' => $payload->data->notes,
            'status' => OrderStatus::Pending,
        ]);

        return $next($payload);
    }
}
