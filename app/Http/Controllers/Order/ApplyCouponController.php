<?php

namespace App\Http\Controllers\Order;

use App\Enums\CouponType;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Number;

class ApplyCouponController extends Controller
{
    public function __invoke(ApplyCouponRequest $request, CouponService $couponService): JsonResponse
    {
        $validated = $request->validated();
        $result = $couponService->validate($validated['code'], (float) $validated['subtotal']);

        if (! $result['valid']) {
            return response()->json(['error' => $result['error']], 422);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'success' => true,
            'coupon_id' => $coupon?->id,
            'code' => $coupon?->code,
            'discount_amount' => $result['discount'],
            'label' => $coupon?->type === CouponType::Percentage
                ? Number::format($coupon->value ?? 0) . '% off'
                : Number::currency((float) ($coupon->value ?? 0)) . ' off',
        ]);
    }
}
