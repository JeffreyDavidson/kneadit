<?php

namespace App\Actions\Orders;

use App\Events\Orders\OrderModified;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Services\Audit\ActorContext;
use App\Services\Orders\CheckOrderStockAvailability;
use App\Services\Orders\ModifiedOrderPricing;
use App\Services\Orders\OrderModificationGuard;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModifyOrder
{
    public function __construct(
        private readonly OrderModificationGuard $guard,
        private readonly CheckOrderStockAvailability $checkStock,
        private readonly ModifiedOrderPricing $pricing,
        private readonly AdjustOrderDiscountLedgers $adjustLedgers,
    ) {}

    /**
     * Modify quantities (and optionally tip) on an existing order.
     *
     * @param  array<int, array{order_item_id: int, quantity: int}>  $items
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

            $newSubtotal = $order->orderItems->reduce(
                fn (Money $carry, OrderItem $item): Money => $carry->add($item->unit_price->multiply($item->quantity)),
                Money::zero(),
            );

            $this->pricing->assertMinimumsMet($order, $previousSubtotal, $newSubtotal);

            // Discounts and the gift card were sized for the order as placed,
            // so re-price them against the new subtotal. The placement values
            // are recorded on first edit so later edits never drift.
            $originalSubtotal = $order->original_subtotal ?? $previousSubtotal;
            $originalDiscount = $order->original_discount_amount ?? $order->discount_amount;
            $previousGiftCard = $order->gift_card_amount;

            $newDiscount = $this->pricing->discount($originalDiscount, $originalSubtotal, $newSubtotal);
            $beforeGiftCard = $newSubtotal
                ->add($order->delivery_fee)
                ->subtract($newDiscount)
                ->max(Money::zero());
            $newGiftCard = $this->pricing->giftCardAmount($previousGiftCard, $beforeGiftCard);
            $tip = $tipAmount !== null
                ? Money::fromDollars(max(0.0, $tipAmount))
                : $order->tip_amount;

            ($this->adjustLedgers)($order, $order->discount_amount, $newDiscount, $previousGiftCard, $newGiftCard);

            $order->forceFill([
                'original_subtotal' => $originalSubtotal,
                'original_discount_amount' => $originalDiscount,
                'subtotal' => $newSubtotal,
                'discount_amount' => $newDiscount,
                'gift_card_amount' => $newGiftCard,
                'tip_amount' => $tip,
                'total' => $beforeGiftCard->subtract($newGiftCard)->add($tip),
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
