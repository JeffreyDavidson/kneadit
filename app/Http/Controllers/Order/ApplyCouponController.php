<?php

namespace App\Http\Controllers\Order;

use App\Enums\CouponType;
use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\Request;

class ApplyCouponController extends Controller
{
    /**
     * Validate and apply a coupon code via AJAX.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
            'subtotal' => ['required', 'numeric', 'min:0'],
        ]);

        $couponService = new CouponService;
        $result = $couponService->validate($request->input('code'), (float) $request->input('subtotal'));

        if (! $result['valid']) {
            return response()->json(['error' => $result['error']], 422);
        }

        $coupon = $result['coupon'];

        return response()->json([
            'success' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount_amount' => $result['discount'],
            'label' => $coupon->type === CouponType::Percentage
                ? number_format($coupon->value, 0).'% off'
                : '$'.number_format($coupon->value, 2).' off',
        ]);
    }
}
