<?php

namespace App\Actions\GiftCards;

use App\DataTransferObjects\GiftCards\CreateGiftCardData;
use App\Enums\Financial\GiftCardTransactionType;
use App\Models\Financial\GiftCard;
use App\Services\GiftCards\GiftCardService;
use Illuminate\Support\Facades\DB;

class CreateGiftCard
{
    public function __construct(
        protected GiftCardService $giftCardService,
    ) {}

    public function __invoke(CreateGiftCardData $data): GiftCard
    {
        return DB::transaction(function () use ($data) {
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
        });
    }
}
