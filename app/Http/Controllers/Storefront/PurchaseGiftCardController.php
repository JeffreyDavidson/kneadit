<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Services\GiftCardService;
use Illuminate\Http\Request;

class PurchaseGiftCardController extends Controller
{
    /**
     * Purchase a new gift card.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'purchaser_name' => ['required', 'string', 'max:255'],
            'purchaser_email' => ['required', 'email', 'max:255'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:1000'],
            'initial_balance' => ['required', 'numeric', 'min:1', 'max:500'],
        ]);

        if (isset($validated['message'])) {
            $validated['message'] = strip_tags($validated['message']);
        }

        $service = new GiftCardService;
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
