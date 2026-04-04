<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckGiftCardBalanceRequest;
use App\Http\Responses\ApiResponse;
use App\Services\GiftCards\GiftCardService;
use Illuminate\Http\JsonResponse;

class CheckGiftCardBalanceController extends Controller
{
    public function __invoke(CheckGiftCardBalanceRequest $request, GiftCardService $service): JsonResponse
    {
        $card = $service->checkBalance($request->code);

        if (! $card) {
            return ApiResponse::error('Gift card not found.', 404);
        }

        return ApiResponse::success([
            'current_balance' => $card->current_balance,
            'expires_at' => $card->expires_at?->format('M j, Y'),
            'is_usable' => $card->is_usable,
        ], 'Gift card balance retrieved successfully.');
    }
}
