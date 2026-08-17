<?php

namespace App\Http\Controllers\Api;

use App\Enums\Financial\CouponType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ApplyDiscountRequest;
use App\Http\Resources\CouponValidationResource;
use App\Services\Coupon\CouponService;
use Illuminate\Validation\ValidationException;

class CouponValidationController extends Controller
{
    public function __invoke(ApplyDiscountRequest $request, CouponService $couponService): CouponValidationResource
    {
        $code = $request->string('code')->toString();
        $result = $couponService->validate($code, $request->float('subtotal'));

        if (! $result->valid) {
            throw ValidationException::withMessages([
                'code' => $result->errorMessage(),
            ]);
        }

        $coupon = $result->coupon;

        $value = $coupon?->type === CouponType::Percentage
            ? $coupon->percentage?->value()
            : $coupon?->fixed_amount?->dollars();

        return new CouponValidationResource([
            'code' => $code,
            'valid' => true,
            'discount_amount' => $result->discount,
            'type' => $coupon?->type?->value,
            'value' => $value,
        ]);
    }
}
