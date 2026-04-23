<?php

namespace App\Actions\GiftCards;

use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\GiftCard;
use Illuminate\Support\Facades\DB;

class AddGiftCardCredit
{
    public function __invoke(GiftCard $card, float $amount, string $notes = 'Credit added', ?int $orderId = null): GiftCard
    {
        return DB::transaction(function () use ($card, $amount, $notes, $orderId) {
            // current_balance is bigint cents (migration 2026_04_22_223000);
            // the public API takes dollars, so convert at the boundary.
            GiftCard::query()->whereKey($card->id)->increment('current_balance', (int) round($amount * 100));

            $card->transactions()->create([
                'amount' => $amount,
                'type' => GiftCardTransactionType::Refund,
                'order_id' => $orderId,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            return $card->refresh();
        });
    }
}
