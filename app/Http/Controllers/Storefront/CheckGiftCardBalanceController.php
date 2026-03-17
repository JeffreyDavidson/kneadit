<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\GiftCardService;
use Illuminate\Http\Request;

class CheckGiftCardBalanceController extends Controller
{
    /**
     * Check the balance of a gift card.
     */
    public function __invoke(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $service = new GiftCardService;
        $card = $service->checkBalance($request->code);

        if (! $card) {
            return response()->json(['success' => false, 'error' => 'Gift card not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'current_balance' => $card->current_balance,
            'expires_at' => $card->expires_at?->format('M j, Y'),
            'is_usable' => $card->isUsable(),
        ]);
    }
}
