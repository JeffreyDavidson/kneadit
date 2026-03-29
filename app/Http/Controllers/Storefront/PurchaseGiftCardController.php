<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\GiftCards\CreateGiftCard;
use App\DataTransferObjects\CreateGiftCardData;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseGiftCardRequest;
use Illuminate\Http\JsonResponse;

class PurchaseGiftCardController extends Controller
{
    /**
     * Purchase a new gift card.
     */
    public function __invoke(PurchaseGiftCardRequest $request, CreateGiftCard $createGiftCard): JsonResponse
    {
        $validated = $request->validated();

        $card = $createGiftCard(CreateGiftCardData::fromArray($validated));

        return response()->json([
            'success' => true,
            'gift_card' => [
                'code' => $card->code,
                'balance' => $card->current_balance,
            ],
        ]);
    }
}
