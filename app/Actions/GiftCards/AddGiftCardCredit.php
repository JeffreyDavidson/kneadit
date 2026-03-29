<?php

namespace App\Actions\GiftCards;

use App\Enums\GiftCardTransactionType;
use App\Models\GiftCard;

class AddGiftCardCredit
{
    public function __invoke(GiftCard $card, float $amount, string $notes = 'Credit added'): GiftCard
    {
        $card->increment('current_balance', $amount);

        $card->transactions()->create([
            'amount' => $amount,
            'type' => GiftCardTransactionType::Refund,
            'notes' => $notes,
            'created_at' => now(),
        ]);

        return $card->refresh();
    }
}
