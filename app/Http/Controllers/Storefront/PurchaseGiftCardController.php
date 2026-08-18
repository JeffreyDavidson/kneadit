<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\GiftCards\CreateGiftCard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PurchaseGiftCardRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class PurchaseGiftCardController extends Controller
{
    public function __invoke(PurchaseGiftCardRequest $request, CreateGiftCard $createGiftCard): JsonResponse
    {
        $card = $createGiftCard($request->toData());

        $message = settingsPageContent('gift_cards')['flash_purchased']
            ?? 'Gift card purchased successfully.';

        return ApiResponse::success([
            'code' => $card->code,
            'balance' => $card->current_balance->dollars(),
        ], $message);
    }
}
