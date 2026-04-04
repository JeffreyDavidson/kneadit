<?php

namespace App\Pipes\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
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
            'gift_card_id' => $payload->giftCardId,
            'gift_card_amount' => $payload->giftCardAmount,
            'total' => $payload->total,
            'notes' => $payload->data->notes,
            'status' => OrderStatus::Pending,
        ]);

        return $next($payload);
    }
}
