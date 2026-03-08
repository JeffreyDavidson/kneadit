<?php

namespace App\Services;

use App\Models\GiftCard;
use Illuminate\Support\Str;

class GiftCardService
{
    public function create(array $data): GiftCard
    {
        $card = GiftCard::create([
            'code' => $this->generateCode(),
            'initial_balance' => $data['initial_balance'],
            'current_balance' => $data['initial_balance'],
            'purchaser_name' => $data['purchaser_name'],
            'purchaser_email' => $data['purchaser_email'],
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_email' => $data['recipient_email'] ?? null,
            'message' => $data['message'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        $card->transactions()->create([
            'amount' => $data['initial_balance'],
            'type' => 'purchase',
            'notes' => 'Initial purchase',
            'created_at' => now(),
        ]);

        return $card;
    }

    public function checkBalance(string $code): ?GiftCard
    {
        return GiftCard::where('code', strtoupper(str_replace('-', '', $code)))->first()
            ?? GiftCard::where('code', strtoupper(trim($code)))->first();
    }

    public function redeem(string $code, float $amount, ?int $orderId = null): array
    {
        $card = GiftCard::where('code', $code)->firstOrFail();

        if (!$card->isUsable()) {
            return ['success' => false, 'error' => 'This gift card is not valid.'];
        }

        if ($amount > (float) $card->current_balance) {
            $amount = (float) $card->current_balance;
        }

        $card->decrement('current_balance', $amount);

        $card->transactions()->create([
            'amount' => -$amount,
            'type' => 'redemption',
            'order_id' => $orderId,
            'notes' => $orderId ? "Applied to order #{$orderId}" : 'Redemption',
            'created_at' => now(),
        ]);

        return [
            'success' => true,
            'amount_applied' => $amount,
            'remaining_balance' => (float) $card->fresh()->current_balance,
        ];
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

        return $card->fresh();
    }

    public function generateCode(): string
    {
        do {
            $raw = strtoupper(Str::random(16));
            // Ensure only alphanumeric
            $raw = preg_replace('/[^A-Z0-9]/', '', $raw . Str::random(4));
            $raw = substr($raw, 0, 16);
            $code = implode('-', str_split($raw, 4));
        } while (GiftCard::where('code', $code)->exists());

        return $code;
    }
}
