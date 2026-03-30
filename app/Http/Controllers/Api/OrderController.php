<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrder;
use App\Enums\DeliveryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiOrderRequest;
use App\Http\Responses\ApiResponse;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __invoke(StoreApiOrderRequest $request, CreateOrder $createOrder, CouponService $couponService): JsonResponse
    {
        $validated = $request->validated();

        $couponId = null;
        if (! empty($validated['coupon_code'])) {
            $result = $couponService->validate($validated['coupon_code'], 0);
            if ($result->valid) {
                $couponId = $result->coupon?->id;
            }
        }

        $validated['delivery_type'] = $validated['delivery_type'] ?? DeliveryType::Pickup->value;
        $validated['coupon_id'] = $couponId;

        $order = $createOrder($request->toData());

        if (! $order) {
            return ApiResponse::error('This date is fully booked or no valid items in order.');
        }

        return ApiResponse::created([
            'order_number' => $order->order_number,
            'total' => $order->total,
            'status' => $order->status,
        ], 'Order submitted successfully.');
    }
}
