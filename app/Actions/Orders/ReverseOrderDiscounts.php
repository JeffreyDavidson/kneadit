<?php

namespace App\Actions\Orders;

use App\Enums\Financial\CouponTransactionType;
use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Orders\Order;
use Illuminate\Database\UniqueConstraintViolationException;
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
        if (! $order->coupon_id || ! $order->discount_amount->isPositive()) {
            return;
        }

        try {
            CouponTransaction::query()->create([
                'coupon_id' => $order->coupon_id,
                'order_id' => $order->id,
                'amount' => -$order->discount_amount->dollars(),
                'type' => CouponTransactionType::Reversal,
                'notes' => $reason,
                'created_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return;
        }

        // Atomic decrement clamped at 0 — read-modify-write would race with
        // concurrent reversals or with the increment in ApplyCoupon. The
        // unique-constraint guard on the reversal transaction (line 39) keeps
        // double-reverses idempotent; the where('used_count', '>', 0) here is
        // belt-and-suspenders against ever going negative.
        if ($order->coupon) {
            Coupon::query()
                ->whereKey($order->coupon->id)
                ->where('used_count', '>', 0)
                ->decrement('used_count');
        }
    }

    private function reverseGiftCard(Order $order, string $reason): void
    {
        if (! $order->gift_card_id || ! $order->gift_card_amount->isPositive()) {
            return;
        }

        try {
            GiftCardTransaction::query()->create([
                'gift_card_id' => $order->gift_card_id,
                'order_id' => $order->id,
                'amount' => $order->gift_card_amount->dollars(),
                'type' => GiftCardTransactionType::Refund,
                'notes' => $reason,
                'created_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return;
        }

        if ($order->giftCard) {
            // current_balance is bigint cents (migration 2026_04_22_223000); pass cents
            // straight from the Money VO instead of converting back through dollars.
            GiftCard::query()->whereKey($order->giftCard->id)->increment('current_balance', $order->gift_card_amount->cents());
        }
    }
}
