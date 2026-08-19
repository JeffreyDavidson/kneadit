<?php

namespace App\Queries\Orders;

use App\Enums\Orders\OrderStatus;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
use Illuminate\Support\Arr;

final class OrderFunnelQuery
{
    /** @return list<array<string, mixed>> */
    public function get(): array
    {
        $aggregates = Order::query()
            ->selectRaw('status, COUNT(*) as order_count, COALESCE(SUM(total), 0) as total_cents')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return array_values(array_map(function (OrderStatus $status) use ($aggregates): array {
            $attributes = $aggregates->get($status->value)?->getAttributes() ?? [];
            $count = Arr::integer($attributes, 'order_count', 0);
            $total = Arr::integer($attributes, 'total_cents', 0);

            return [
                ...$status->toFunnelStage($count),
                'total_formatted' => Money::fromCents($total)->formatted(),
            ];
        }, OrderStatus::trackableStatuses()));
    }
}
