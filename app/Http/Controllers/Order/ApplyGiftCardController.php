<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Services\GiftCardService;
use Illuminate\Http\Request;

class ApplyGiftCardController extends Controller
{
    /**
     * Validate and apply a gift card code via AJAX.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $service = new GiftCardService;
        $card = $service->checkBalance($request->code);

        if (! $card) {
            return response()->json(['error' => 'Gift card not found.'], 422);
        }

        if (! $card->isUsable()) {
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
