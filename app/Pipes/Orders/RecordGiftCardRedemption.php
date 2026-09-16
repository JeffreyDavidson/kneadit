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
        if (! $payload->giftCardId || ! $payload->giftCardAmount->isPositive()) {
            return $next($payload);
        }

        assert($payload->order !== null, 'Order must be persisted before RecordGiftCardRedemption');

        $giftCard = GiftCard::query()->find($payload->giftCardId);

        if ($giftCard) {
            ($this->redeemGiftCard)($giftCard->code, $payload->giftCardAmount->dollars(), $payload->order->id);
        }

        return $next($payload);
    }
}
