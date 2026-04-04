<?php

namespace App\Actions\Orders;

use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\DB;

class ReverseOrderDiscounts
{
    public function __invoke(Order $order, string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            $this->reverseCoupon($order, $reason);
            $this->reverseGiftCard($order, $reason);
        });
    }

    private function reverseCoupon(Order $order, string $reason): void
    {
        if (! $order->coupon_id || $order->discount_amount <= 0) {
            return;
        }

        $alreadyReversed = CouponTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', CouponTransactionType::Reversal)
            ->exists();

        if ($alreadyReversed) {
            return;
        }

        $order->coupon?->decrement('used_count');

        CouponTransaction::query()->create([
            'coupon_id' => $order->coupon_id,
            'order_id' => $order->id,
            'amount' => -$order->discount_amount,
            'type' => CouponTransactionType::Reversal,
            'notes' => $reason,
            'created_at' => now(),
        ]);
    }

    private function reverseGiftCard(Order $order, string $reason): void
    {
        if (! $order->gift_card_id || $order->gift_card_amount <= 0) {
            return;
        }

        $alreadyReversed = GiftCardTransaction::query()
            ->where('order_id', $order->id)
            ->where('type', GiftCardTransactionType::Refund)
            ->exists();

        if ($alreadyReversed) {
            return;
        }

        $order->giftCard?->increment('current_balance', $order->gift_card_amount);

        GiftCardTransaction::query()->create([
            'gift_card_id' => $order->gift_card_id,
            'order_id' => $order->id,
            'amount' => $order->gift_card_amount,
            'type' => GiftCardTransactionType::Refund,
            'notes' => $reason,
            'created_at' => now(),
        ]);
    }
}
