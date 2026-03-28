<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseGiftCardRequest;
use App\Services\GiftCardService;
use Illuminate\Http\JsonResponse;

class PurchaseGiftCardController extends Controller
{
    /**
     * Purchase a new gift card.
     */
    public function __invoke(PurchaseGiftCardRequest $request, GiftCardService $service): JsonResponse
    {
        $validated = $request->validated();

        $card = $service->create($validated);

        return response()->json([
            'success' => true,
            'gift_card' => [
                'code' => $card->code,
                'balance' => $card->current_balance,
            ],
        ]);
    }
}
