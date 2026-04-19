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
            $amount = min($giftCard->current_balance->dollars(), $payload->total);

            if ($amount > 0) {
                $payload->giftCardId = $giftCard->id;
                $payload->giftCardAmount = $amount;
                $payload->total = max(0, $payload->total - $amount);
            }
        }

        return $next($payload);
    }
}
