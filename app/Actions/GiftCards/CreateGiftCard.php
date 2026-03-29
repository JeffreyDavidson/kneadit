<?php

namespace App\Actions\GiftCards;

use App\DataTransferObjects\CreateGiftCardData;
use App\Enums\GiftCardTransactionType;
use App\Models\GiftCard;
use App\Services\GiftCardService;

class CreateGiftCard
{
    public function __construct(
        protected GiftCardService $giftCardService,
    ) {}

    public function __invoke(CreateGiftCardData $data): GiftCard
    {
        $card = GiftCard::query()->create([
            'code' => $this->giftCardService->generateCode(),
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
}
