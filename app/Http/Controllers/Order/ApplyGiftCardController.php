<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ApplyDiscountRequest;
use App\Http\Responses\ApiResponse;
use App\Services\GiftCards\GiftCardService;
use Illuminate\Http\JsonResponse;

class ApplyGiftCardController extends Controller
{
    public function __invoke(ApplyDiscountRequest $request, GiftCardService $service): JsonResponse
    {
        $card = $service->checkBalance($request->code);

        if (! $card) {
            return ApiResponse::error('Gift card not found.');
        }

        if (! $card->is_usable) {
            return ApiResponse::error('This gift card is no longer valid.');
        }

        return ApiResponse::success([
            'gift_card_id' => $card->id,
            'code' => $card->code,
            'available_balance' => (float) $card->current_balance,
            'applicable_amount' => min((float) $card->current_balance, (float) $request->validated('subtotal')),
        ], 'Gift card applied successfully.');
    }
}
