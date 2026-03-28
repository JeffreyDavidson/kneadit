<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyGiftCardRequest;
use App\Services\GiftCardService;
use Illuminate\Http\JsonResponse;

class ApplyGiftCardController extends Controller
{
    /**
     * Validate and apply a gift card code via AJAX.
     */
    public function __invoke(ApplyGiftCardRequest $request, GiftCardService $service): JsonResponse
    {

        $card = $service->checkBalance($request->code);

        if (! $card) {
            return response()->json(['error' => 'Gift card not found.'], 422);
        }

        if (! $card->is_usable) {
            return response()->json(['error' => 'This gift card is no longer valid.'], 422);
        }

        $applicable = min((float) $card->current_balance, (float) $request->subtotal);

        return response()->json([
            'success' => true,
            'gift_card_id' => $card->id,
            'code' => $card->code,
            'available_balance' => (float) $card->current_balance,
            'applicable_amount' => $applicable,
        ]);
    }
}
