<?php

namespace App\Pipes\Orders;

use App\Actions\GiftCards\RedeemGiftCard;
use App\Models\GiftCard;
use Closure;

class ApplyGiftCard
{
    public function __construct(
        private RedeemGiftCard $redeemGiftCard,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->data->giftCardId) {
            return $next($payload);
        }

        $giftCard = GiftCard::query()->lockForUpdate()->find($payload->data->giftCardId);

        if ($giftCard && $giftCard->is_usable) {
            $amount = min((float) $giftCard->current_balance, (float) $payload->order->total);
            if ($amount > 0) {
                ($this->redeemGiftCard)($giftCard->code, $amount, $payload->order->id);
                $payload->order->update(['total' => max(0, (float) $payload->order->total - $amount)]);
            }
        }

        return $next($payload);
    }
}
