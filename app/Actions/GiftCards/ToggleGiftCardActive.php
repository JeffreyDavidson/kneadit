<?php

namespace App\Actions\GiftCards;

use App\Models\GiftCard;

class ToggleGiftCardActive
{
    public function __invoke(GiftCard $giftCard): void
    {
        $giftCard->update(['is_active' => ! $giftCard->is_active]);
    }
}
