<?php

namespace App\Http\Controllers\Order;

use App\Actions\Orders\ModifyOrder;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ModifyOrderRequest;
use App\Models\Orders\Order;
use Illuminate\Http\RedirectResponse;

class ModifyOrderController extends Controller
{
    public function __invoke(ModifyOrderRequest $request, Order $order, ModifyOrder $modifyOrder): RedirectResponse
    {
        try {
            $modifyOrder(
                $order,
                $this->items($request->array('items')),
                $request->filled('tip_amount')
                    ? $request->float('tip_amount')
                    : null,
            );
        } catch (OrderNotModifiableException $e) {
            return back()->withErrors(['items' => $e->reason]);
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Your order was updated.');
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, array{order_item_id: int, quantity: int}>
     */
    private function items(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $orderItemId = $item['order_item_id'] ?? null;
            $quantity = $item['quantity'] ?? null;

            if (is_int($orderItemId) && is_int($quantity)) {
                $normalized[] = ['order_item_id' => $orderItemId, 'quantity' => $quantity];
            }
        }

        return $normalized;
    }
}
