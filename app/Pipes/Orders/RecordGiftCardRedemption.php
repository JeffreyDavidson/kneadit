<?php

namespace App\Pipes\Orders;

use App\Actions\GiftCards\RedeemGiftCard;
use App\Models\Financial\GiftCard;
use Closure;

class RecordGiftCardRedemption
{
    public function __construct(
        private RedeemGiftCard $redeemGiftCard,
    ) {}

    public function handle(OrderPipelineData $payload, Closure $next): mixed
    {
        if (! $payload->giftCardId || $payload->giftCardAmount <= 0) {
            return $next($payload);
        }

        $giftCard = GiftCard::query()->find($payload->giftCardId);

        if ($giftCard) {
            ($this->redeemGiftCard)($giftCard->code, $payload->giftCardAmount, $payload->order->id);
        }

        return $next($payload);
    }
}
