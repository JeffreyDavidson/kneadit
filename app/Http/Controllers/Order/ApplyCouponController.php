<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Responses\ApiResponse;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;

class ApplyCouponController extends Controller
{
    public function __invoke(ApplyCouponRequest $request, CouponService $couponService): JsonResponse
    {
        $result = $couponService->validate($request->validated('code'), (float) $request->validated('subtotal'));

        if (! $result->valid) {
            return ApiResponse::error($result->error);
        }

        $coupon = $result->coupon;

        return ApiResponse::success([
            'coupon_id' => $coupon?->id,
            'code' => $coupon?->code,
            'discount_amount' => $result->discount,
            'label' => $coupon?->type?->formatDiscount((float) ($coupon?->value ?? 0)),
        ], 'Coupon applied successfully.');
    }
}
