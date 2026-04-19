<?php

namespace App\Actions\GiftCards;

use App\DataTransferObjects\GiftCards\GiftCardRedemptionResult;
use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\GiftCard;
use Illuminate\Support\Facades\DB;

class RedeemGiftCard
{
    public function __invoke(string $code, float $amount, ?int $orderId = null): GiftCardRedemptionResult
    {
        return DB::transaction(function () use ($code, $amount, $orderId) {
            $card = GiftCard::query()->lockForUpdate()->where('code', $code)->firstOrFail();

            if (! $card->is_usable) {
                return GiftCardRedemptionResult::failed('This gift card is not valid.');
            }

            if ($amount > $card->current_balance->dollars()) {
                $amount = $card->current_balance->dollars();
            }

            GiftCard::query()->whereKey($card->id)->decrement('current_balance', $amount);

            $card->transactions()->create([
                'amount' => -$amount,
                'type' => GiftCardTransactionType::Redemption,
                'order_id' => $orderId,
                'notes' => $orderId ? "Applied to order #{$orderId}" : 'Redemption',
                'created_at' => now(),
            ]);

            return GiftCardRedemptionResult::redeemed($amount, $card->refresh()->current_balance->dollars());
        });
    }
}
