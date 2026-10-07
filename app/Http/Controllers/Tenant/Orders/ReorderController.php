<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Orders\Order;
use App\Services\Orders\OrderLineRefresher;
use Illuminate\Http\JsonResponse;

class ReorderController extends Controller
{
    public function __invoke(Order $order, OrderLineRefresher $refresher): JsonResponse
    {
        $refreshed = $refresher->refresh($order->orderItems()->with('product')->get());

        return ApiResponse::success([
            'items' => array_map(fn (array $line): array => [
                'product_id' => $line['product_id'],
                'product_name' => $line['name'],
                'price' => $line['price'],
                'quantity' => $line['quantity'],
            ], $refreshed->items),
            'removed_items' => $refreshed->removedNames,
        ], 'Reorder data retrieved successfully.');
    }
}
