<?php

namespace App\Actions\Orders;

use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use App\ValueObjects\Money;

/**
 * Keeps the coupon and gift card ledgers in step with a re-priced order, so a
 * later cancellation (ReverseOrderDiscounts) reverses exactly what is still
 * applied. Call inside the transaction that saves the new order totals.
 */
class AdjustOrderDiscountLedgers
{
    public function __invoke(Order $order, Money $previousDiscount, Money $newDiscount, Money $previousGiftCard, Money $newGiftCard): void
    {
        $this->scaleCouponUsage($order, $previousDiscount, $newDiscount);
        $this->creditGiftCard($order, $previousGiftCard->subtract($newGiftCard));
    }

    private function scaleCouponUsage(Order $order, Money $previousDiscount, Money $newDiscount): void
    {
        if (! $order->coupon_id || ! $previousDiscount->isPositive()) {
            return;
        }

        $usage = CouponTransaction::query()
            ->where('coupon_id', $order->coupon_id)
            ->where('order_id', $order->id)
            ->where('type', CouponTransactionType::Usage)
            ->first();

        if (! $usage) {
            return;
        }

        // The usage row holds only the coupon's share of the aggregate
        // discount, so it moves by the same proportion.
        $usage->amount = $usage->amount->multiply($newDiscount->cents() / $previousDiscount->cents());
        $usage->save();
    }

    private function creditGiftCard(Order $order, Money $unusedAmount): void
    {
        if (! $order->gift_card_id || ! $unusedAmount->isPositive()) {
            return;
        }

        // Atomic increment in cents, as ReverseOrderDiscounts does.
        GiftCard::query()->whereKey($order->gift_card_id)->increment('current_balance', $unusedAmount->cents());

        $redemption = GiftCardTransaction::query()
            ->where('gift_card_id', $order->gift_card_id)
            ->where('order_id', $order->id)
            ->where('type', GiftCardTransactionType::Redemption)
            ->first();

        if (! $redemption) {
            return;
        }

        // Redemptions are stored negative; shrink the draw by the credit.
        $redemption->amount = $redemption->amount->add($unusedAmount);
        $redemption->save();
    }
}
