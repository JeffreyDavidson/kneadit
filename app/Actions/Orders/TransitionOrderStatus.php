<?php

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Models\Orders\Order;
use App\Services\Orders\OrderStatusEffectDispatcher;
use Illuminate\Support\Facades\DB;

class TransitionOrderStatus
{
    /** @var array<string, array<string>> */
    private const TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
        OrderStatus::Confirmed->value => [OrderStatus::Baking->value, OrderStatus::Cancelled->value],
        OrderStatus::Baking->value => [OrderStatus::Ready->value, OrderStatus::Cancelled->value],
        OrderStatus::Ready->value => [OrderStatus::Delivered->value],
    ];

    public function __construct(
        private OrderStatusEffectDispatcher $effects,
    ) {}

    public function __invoke(Order $order, OrderStatus $to): Order
    {
        $from = $order->status;
        $allowed = self::TRANSITIONS[$from->value] ?? [];

        throw_unless(in_array($to->value, $allowed), InvalidOrderTransitionException::class, $order, $from, $to);

        DB::transaction(function () use ($order, $from, $to) {
            $order->update(['status' => $to]);
            $this->effects->dispatch($order, $from, $to);
        });

        return $order;
    }

    /**
     * @return array<OrderStatus>
     */
    public static function allowedTransitions(Order $order): array
    {
        $allowed = self::TRANSITIONS[$order->status->value] ?? [];

        return array_map(fn (string $s) => OrderStatus::from($s), $allowed);
    }
}
