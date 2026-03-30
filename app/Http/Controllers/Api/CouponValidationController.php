<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Responses\ApiResponse;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;

class CouponValidationController extends Controller
{
    public function __invoke(ApplyCouponRequest $request, CouponService $couponService): JsonResponse
    {
        $validated = $request->validated();

        $result = $couponService->validate($validated['code'], (float) $validated['subtotal']);

        if (! $result->valid) {
            return ApiResponse::success(['valid' => false, 'discount_amount' => 0, 'type' => null, 'value' => null], $result->error);
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
