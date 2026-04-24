<?php

namespace App\Actions\Orders;

use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderStatusChanged;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Models\Orders\Order;
use App\Services\Inventory\InventoryManager;
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
        private InventoryManager $inventoryManager,
        private ReverseOrderDiscounts $reverseOrderDiscounts,
    ) {}

    public function __invoke(Order $order, OrderStatus $to): Order
    {
        $from = $order->status;
        $allowed = self::TRANSITIONS[$from->value] ?? [];

        throw_unless(in_array($to->value, $allowed), InvalidOrderTransitionException::class, $order, $from, $to);

        DB::transaction(function () use ($order, $from, $to) {
            $order->update(['status' => $to]);

            if ($to === OrderStatus::Baking) {
                $this->inventoryManager->deductForOrder($order);
            }

            if ($to === OrderStatus::Cancelled) {
                ($this->reverseOrderDiscounts)($order, "Order cancelled (was {$from->value})");

                // Restock ingredients only when cancelling from Baking — that's the
                // only state where deduction has run. Pending and Confirmed haven't
                // deducted yet, and Ready can only transition to Delivered, never
                // to Cancelled (state-machine guard above).
                if ($from === OrderStatus::Baking) {
                    $this->inventoryManager->restockForOrder($order);
                }
            }
        });

        OrderStatusChanged::dispatch($order, $from, $to);

        if ($to === OrderStatus::Delivered) {
            OrderDelivered::dispatch($order, $from);
        }

        if ($to === OrderStatus::Cancelled) {
            OrderCancelled::dispatch($order, $from);
        }

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
