<?php

namespace App\Services;

use App\DataTransferObjects\CreateGiftCardData;
use App\DataTransferObjects\GiftCardRedemptionResult;
use App\Enums\GiftCardTransactionType;
use App\Models\GiftCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GiftCardService
{
    public function create(CreateGiftCardData $data): GiftCard
    {
        $card = GiftCard::query()->create([
            'code' => $this->generateCode(),
            'initial_balance' => $data->initialBalance,
            'current_balance' => $data->initialBalance,
            'purchaser_name' => $data->purchaserName,
            'purchaser_email' => $data->purchaserEmail,
            'recipient_name' => $data->recipientName,
            'recipient_email' => $data->recipientEmail,
            'message' => $data->message,
            'expires_at' => $data->expiresAt,
        ]);

        $card->transactions()->create([
            'amount' => $data->initialBalance,
            'type' => GiftCardTransactionType::Purchase,
            'notes' => 'Initial purchase',
            'created_at' => now(),
        ]);

        return $card;
    }

    public function checkBalance(string $code): ?GiftCard
    {
        return GiftCard::query()->where('code', Str::upper(Str::replace('-', '', $code)))->first()
            ?? GiftCard::query()->where('code', Str::upper(trim($code)))->first();
    }

    public function redeem(string $code, float $amount, ?int $orderId = null): GiftCardRedemptionResult
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

    public function addCredit(GiftCard $card, float $amount, string $notes = 'Credit added'): GiftCard
    {
        $card->increment('current_balance', $amount);

        $card->transactions()->create([
            'amount' => $amount,
            'type' => 'refund',
            'notes' => $notes,
            'created_at' => now(),
        ]);

        return $card->refresh();
    }

    public function generateCode(): string
    {
        do {
            $raw = Str::upper(Str::random(16));
            // Ensure only alphanumeric
            $raw = (string) preg_replace('/[^A-Z0-9]/', '', $raw . Str::random(4));
            $raw = substr($raw, 0, 16);
            $code = implode('-', str_split($raw, 4));
        } while (GiftCard::query()->where('code', $code)->exists());

        return $code;
    }
}
