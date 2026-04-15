<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiOrderRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __invoke(StoreApiOrderRequest $request, CreateOrder $createOrder): JsonResponse
    {
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
