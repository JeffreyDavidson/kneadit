<?php

namespace App\Pipes\Orders;

use App\Models\Financial\GiftCard;
use Closure;

/**
 * Draws from the gift card last, once every discount and perk has settled, and
 * only against the items and delivery. The tip is never paid by a gift card, so
 * the card is never debited more than the order's own price.
 */
class ApplyGiftCard
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->data->giftCardId) {
            return $next($payload);
        }

        $giftCard = GiftCard::query()
            ->lockForUpdate()
            ->whereKey($payload->data->giftCardId)
            ->where('code', $payload->data->giftCardCode)
            ->first();

        if ($giftCard && $giftCard->is_usable) {
            $amount = $giftCard->current_balance->min($payload->payableBeforeGiftCard());

            if ($amount->isPositive()) {
                $payload->giftCardId = $giftCard->id;
                $payload->giftCardAmount = $amount;
                $payload->recalculateTotal();
            }
        }

        return $next($payload);
    }
}
