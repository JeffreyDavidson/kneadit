<?php

namespace App\Actions\Orders;

use App\Events\Orders\OrderModified;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Audit\ActorContext;
use App\Services\Orders\CheckOrderStockAvailability;
use App\Services\Orders\OrderModificationGuard;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModifyOrder
{
    public function __construct(
        private OrderModificationGuard $guard,
        private CheckOrderStockAvailability $checkStock,
    ) {}

    /**
     * Modify quantities (and optionally tip) on an existing order.
     *
     * @param array<int, array{order_item_id: int, quantity: int}> $items
     */
    public function __invoke(Order $order, array $items, ?float $tipAmount = null): Order
    {
        throw_unless($this->guard->canModify($order), OrderNotModifiableException::class, $order, 'window expired or order ineligible');

        $previousSubtotal = $order->subtotal;
        $previousTotal = $order->total;

        return DB::transaction(function () use ($order, $items, $tipAmount, $previousSubtotal, $previousTotal): Order {
            $order->load('orderItems');
            $itemsById = $order->orderItems->keyBy('id');

            foreach ($items as $update) {
                $item = $itemsById->get($update['order_item_id']);
                if (! $item) {
                    continue;
                }
                if ($item->order_id !== $order->id) {
                    continue;
                }

                $newQty = max(0, (int) $update['quantity']);

                if ($newQty === 0) {
                    $item->delete();

                    continue;
                }

                $item->forceFill(['quantity' => $newQty])->save();
            }

            $order->load('orderItems');

            throw_if($order->orderItems->isEmpty(), OrderNotModifiableException::class, $order, 'modification would leave order with no items');

            // Verify the post-modification ingredient draw fits inside current
            // stock. Throws InsufficientStockException (rolling back this
            // transaction) before we recompute totals or fire OrderModified.
            ($this->checkStock)($order);

            $subtotalDollars = $order->orderItems->sum(
                fn (OrderItem $item): float => $item->unit_price->dollars() * $item->quantity,
            );

            $deliveryFeeDollars = $order->delivery_fee->dollars();
            $discountDollars = $order->discount_amount->dollars();
            $giftCardDollars = $order->gift_card_amount->dollars();
            $tipDollars = $tipAmount !== null
                ? max(0.0, $tipAmount)
                : $order->tip_amount->dollars();

            $afterDiscount = max(0.0, $subtotalDollars + $deliveryFeeDollars - $discountDollars);
            $totalDollars = max(0.0, $afterDiscount - $giftCardDollars) + $tipDollars;

            $order->forceFill([
                'subtotal' => Money::fromDollars($subtotalDollars),
                'tip_amount' => Money::fromDollars($tipDollars),
                'total' => Money::fromDollars($totalDollars),
            ])->save();

            Log::info('Order modified', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'previous_total_cents' => $previousTotal->cents(),
                'new_total_cents' => $order->total->cents(),
                'item_changes' => count($items),
                'actor_id' => ActorContext::id(),
                'actor_name' => ActorContext::name(),
            ]);

            event(new OrderModified($order, $previousSubtotal, $previousTotal));

            return $order->refresh();
        });
    }
}
