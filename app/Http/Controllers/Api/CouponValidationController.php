<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ApplyDiscountRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Coupon\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CouponValidationController extends Controller
{
    public function __invoke(ApplyDiscountRequest $request, CouponService $couponService): JsonResponse
    {
        $validated = $request->validated();

        $result = $couponService->validate($validated['code'], (float) $validated['subtotal']);

        if (! $result->valid) {
            throw ValidationException::withMessages([
                'code' => $result->errorMessage(),
            ]);
        }

        $coupon = $result->coupon;

        return ApiResponse::success([
            'valid' => true,
            'discount_amount' => $result->discount,
            'type' => $coupon?->type,
            'value' => $coupon?->value,
        ], 'Coupon is valid.');
    }
}
