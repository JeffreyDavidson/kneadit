<?php

namespace App\Actions\Orders;

use App\Actions\Inventory\NotifyStaffOfNegativeStock;
use App\Enums\Orders\OrderStatus;
use App\Events\Orders\OrderCancelled;
use App\Events\Orders\OrderDelivered;
use App\Events\Orders\OrderStatusChanged;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Models\Orders\Order;
use App\Services\Audit\ActorContext;
use App\Services\Inventory\InventoryManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransitionOrderStatus
{
    /** @var array<string, array<string>> */
    private const array TRANSITIONS = [
        OrderStatus::Pending->value => [OrderStatus::Confirmed->value, OrderStatus::Cancelled->value],
        OrderStatus::Confirmed->value => [OrderStatus::Baking->value, OrderStatus::Cancelled->value],
        OrderStatus::Baking->value => [OrderStatus::Ready->value, OrderStatus::Cancelled->value],
        OrderStatus::Ready->value => [OrderStatus::Delivered->value],
    ];

    public function __construct(
        private readonly InventoryManager $inventoryManager,
        private readonly ReverseOrderDiscounts $reverseOrderDiscounts,
        private readonly NotifyStaffOfNegativeStock $notifyStaffOfNegativeStock,
    ) {}

    public function __invoke(Order $order, OrderStatus $to): Order
    {
        [$from, $shortfalls] = DB::transaction(function () use ($order, $to): array {
            // Two taps or two tabs hold stale copies of the same order, so check the status
            // again on the locked row: whichever transition commits first wins and the other
            // is refused before it can send its emails or webhooks a second time.
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $order->setRawAttributes($locked->getAttributes(), true);
            $from = $order->status;
            $shortfalls = [];

            throw_unless(in_array($to->value, self::TRANSITIONS[$from->value] ?? []), InvalidOrderTransitionException::class, $order, $from, $to);

            $order->update(['status' => $to]);

            if ($to === OrderStatus::Baking) {
                $shortfalls = $this->inventoryManager->deductForOrder($order);
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

            return [$from, $shortfalls];
        });

        ($this->notifyStaffOfNegativeStock)($order, $shortfalls);

        Log::info('Order status transitioned', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'from' => $from->value,
            'to' => $to->value,
            'actor_id' => ActorContext::id(),
            'actor_name' => ActorContext::name(),
        ]);

        event(new OrderStatusChanged($order, $from, $to));

        if ($to === OrderStatus::Delivered) {
            event(new OrderDelivered($order, $from));
        }

        if ($to === OrderStatus::Cancelled) {
            event(new OrderCancelled($order, $from));
        }

        return $order;
    }

    /**
     * @return array<OrderStatus>
     */
    public static function allowedTransitions(Order $order): array
    {
        $allowed = self::TRANSITIONS[$order->status->value] ?? [];

        return array_map(OrderStatus::from(...), $allowed);
    }
}
