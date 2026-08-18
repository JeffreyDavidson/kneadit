<?php

namespace App\Http\Resources;

use App\Models\Orders\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

/** @mixin Order */
class OrderResource extends JsonApiResource
{
    public function toId(Request $request): string
    {
        return (string) $this->resource->getKey();
    }

    public function toType(Request $request): string
    {
        return 'orders';
    }

    /** @return array<string, mixed> */
    public function toAttributes(Request $request): array
    {
        return [
            'order_number' => $this->resource->order_number,
            'total' => $this->resource->total,
            'status' => $this->resource->status->value,
            'payment_status' => $this->resource->payment_status->value,
            'delivery_type' => $this->resource->delivery_type->value,
            'delivery_date' => $this->resource->delivery_date?->toDateString(),
        ];
    }
}
