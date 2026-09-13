<?php

namespace App\Pipes\Orders;

use App\Models\Financial\GiftCard;
use Closure;

class ApplyGiftCard
{
    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->data->giftCardId) {
            return $next($payload);
        }

        $giftCard = GiftCard::query()->lockForUpdate()->find($payload->data->giftCardId);

        if ($giftCard && $giftCard->is_usable) {
            $amount = $giftCard->current_balance->min($payload->total);

            if ($amount->isPositive()) {
                $payload->giftCardId = $giftCard->id;
                $payload->giftCardAmount = $amount;
                $payload->recalculateTotal();
            }
        }

        return $next($payload);
    }
}
