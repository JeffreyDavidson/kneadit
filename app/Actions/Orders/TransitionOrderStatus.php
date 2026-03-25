<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        protected AwardLoyaltyPoints $awardLoyaltyPoints,
        protected DeductIngredients $deductIngredients,
    ) {}

    public function __invoke(Order $order, OrderStatus $to): Order
    {
        $from = $order->status;
        $allowed = self::TRANSITIONS[$from->value] ?? [];

        if (! in_array($to->value, $allowed)) {
            throw new InvalidOrderTransitionException($order, $from, $to);
        }

        DB::transaction(function () use ($order, $to) {
            $order->update(['status' => $to]);

            if ($to === OrderStatus::Baking) {
                try {
                    ($this->deductIngredients)($order);
                } catch (\Exception $e) {
                    Log::warning('Ingredient deduction failed', [
                        'order' => $order->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($to === OrderStatus::Delivered) {
                try {
                    ($this->awardLoyaltyPoints)($order);
                } catch (\Exception $e) {
                    Log::warning('Loyalty points award failed', [
                        'order' => $order->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        OrderStatusChanged::dispatch($order, $from, $to);

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
