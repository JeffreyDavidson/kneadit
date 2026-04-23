<?php

namespace App\Actions\Orders;

use App\Events\Orders\OrderModified;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Orders\Order;
use App\Services\Orders\OrderModificationGuard;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

class ModifyOrder
{
    public function __construct(
        private OrderModificationGuard $guard,
    ) {}

    /**
     * Modify quantities (and optionally tip) on an existing order.
     *
     * @param array<int, array{order_item_id: int, quantity: int}> $items
     */
    public function __invoke(Order $order, array $items, ?float $tipAmount = null): Order
    {
        if (! $this->guard->canModify($order)) {
            throw new OrderNotModifiableException($order, 'window expired or order ineligible');
        }

        $previousSubtotal = $order->subtotal;
        $previousTotal = $order->total;

        return DB::transaction(function () use ($order, $items, $tipAmount, $previousSubtotal, $previousTotal): Order {
            $order->load('orderItems');
            $itemsById = $order->orderItems->keyBy('id');

            foreach ($items as $update) {
                $item = $itemsById->get($update['order_item_id']);
                if (! $item || $item->order_id !== $order->id) {
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

            if ($order->orderItems->isEmpty()) {
                throw new OrderNotModifiableException($order, 'modification would leave order with no items');
            }

            $subtotalDollars = $order->orderItems->sum(
                fn ($item) => $item->unit_price->dollars() * $item->quantity,
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

            event(new OrderModified($order, $previousSubtotal, $previousTotal));

            return $order->refresh();
        });
    }
}
