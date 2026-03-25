<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrder;
use App\Enums\DeliveryType;
use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __invoke(Request $request, CreateOrder $createOrder): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after_or_equal:today'],
            'delivery_time' => ['nullable', 'string'],
            'delivery_type' => ['nullable', Rule::in(DeliveryType::cases())],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'delivery_tier' => ['nullable', 'in:under5,5to10,10to15,over15'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'coupon_code' => ['nullable', 'string'],
        ]);

        // Resolve coupon ID from code if provided
        $couponId = null;
        if (! empty($validated['coupon_code'])) {
            $couponService = new CouponService;
            $result = $couponService->validate($validated['coupon_code'], 0);
            if ($result['valid']) {
                $couponId = $result['coupon']->id;
            }
        }

        // Default delivery_type to pickup for API requests that omit it
        $validated['delivery_type'] = $validated['delivery_type'] ?? DeliveryType::Pickup->value;

        $order = $createOrder($validated, $couponId);

        if (! $order) {
            return response()->json([
                'data' => null,
                'message' => 'This date is fully booked or no valid items in order.',
            ], 422);
        }

        return response()->json([
            'data' => [
                'order_number' => $order->order_number,
                'total' => $order->total,
                'status' => $order->status,
            ],
            'message' => 'Order submitted successfully.',
        ], 201);
    }
}
