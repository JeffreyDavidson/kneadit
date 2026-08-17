<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ApplyDiscountRequest;
use App\Http\Responses\ApiResponse;
use App\Presenters\CouponPresenter;
use App\Services\Coupon\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ApplyCouponController extends Controller
{
    public function __invoke(ApplyDiscountRequest $request, CouponService $couponService): JsonResponse
    {
        $result = $couponService->validate(
            $request->string('code')->toString(),
            $request->float('subtotal'),
        );

        if (! $result->valid) {
            throw ValidationException::withMessages([
                'code' => $result->errorMessage(),
            ]);
        }

        $coupon = $result->coupon;

        return ApiResponse::success([
            'coupon_id' => $coupon?->id,
            'code' => $coupon?->code,
            'discount_amount' => $result->discount,
            'label' => $coupon ? CouponPresenter::for($coupon)->formattedDiscount() : null,
        ], 'Coupon applied successfully.');
    }
}
