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
        $card = $service->checkBalance($request->string('code')->toString());

        if (! $card) {
            return ApiResponse::error('Gift card not found.');
        }

        if (! $card->is_usable) {
            return ApiResponse::error('This gift card is no longer valid.');
        }

        return ApiResponse::success([
            'gift_card_id' => $card->id,
            'code' => $card->code,
            'available_balance' => $card->current_balance->dollars(),
            'applicable_amount' => min($card->current_balance->dollars(), $request->float('subtotal')),
        ], 'Gift card applied successfully.');
    }
}
