<?php

namespace App\Actions\GiftCards;

use App\DataTransferObjects\GiftCardRedemptionResult;
use App\Enums\GiftCardTransactionType;
use App\Models\GiftCard;
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

            if ($amount > (float) $card->current_balance) {
                $amount = (float) $card->current_balance;
            }

            $card->decrement('current_balance', $amount);

            $card->transactions()->create([
                'amount' => -$amount,
                'type' => GiftCardTransactionType::Redemption,
                'order_id' => $orderId,
                'notes' => $orderId ? "Applied to order #{$orderId}" : 'Redemption',
                'created_at' => now(),
            ]);

            return GiftCardRedemptionResult::redeemed($amount, (float) $card->refresh()->current_balance);
        });
    }
}
