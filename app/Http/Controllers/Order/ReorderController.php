<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;

class ReorderController extends Controller
{
    /**
     * Return reorder data for a previous order.
     */
    public function __invoke(Order $order)
    {
        $items = $order->orderItems->map(fn ($item) => [
            'product_id' => $item->product_id,
            'product_name' => $item->product?->name ?? 'Unknown',
            'price' => $item->unit_price,
            'quantity' => $item->quantity,
        ]);

        return response()->json(['items' => $items]);
    }
}
