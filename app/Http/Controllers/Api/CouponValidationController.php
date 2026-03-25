<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;

class CouponValidationController extends Controller
{
    public function __invoke(ApplyCouponRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $couponService = new CouponService;
        $result = $couponService->validate($validated['code'], (float) $validated['subtotal']);

        if (! $result['valid']) {
            return response()->json([
                'data' => ['valid' => false, 'discount_amount' => 0, 'type' => null, 'value' => null],
                'message' => $result['error'],
            ]);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'data' => [
                'valid' => true,
                'discount_amount' => $result['discount'],
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
            'message' => 'Coupon is valid.',
        ]);
    }
}
