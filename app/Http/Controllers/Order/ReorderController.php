<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;

class ReorderController extends Controller
{
    public function __invoke(Order $order): JsonResponse
    {
        $order->load('orderItems.product');

        $items = $order->orderItems->map(fn (OrderItem $item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product->name ?? 'Unknown',
            'price' => $item->unit_price,
            'quantity' => $item->quantity,
        ]);

        return ApiResponse::success([
            'items' => $items,
        ], 'Reorder data retrieved successfully.');
    }
}
