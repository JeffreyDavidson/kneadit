<?php

namespace App\Http\Controllers\Tenant\Orders;

use App\Actions\Orders\ModifyOrder;
use App\Exceptions\Orders\OrderNotModifiableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ModifyOrderRequest;
use App\Models\Orders\Order;
use App\Services\Orders\ItemPayloadNormalizer;
use Illuminate\Http\RedirectResponse;

class ModifyOrderController extends Controller
{
    public function __invoke(
        ModifyOrderRequest $request,
        Order $order,
        ModifyOrder $modifyOrder,
        ItemPayloadNormalizer $itemPayloadNormalizer,
    ): RedirectResponse {
        try {
            $modifyOrder(
                $order,
                $itemPayloadNormalizer->orderItems($request->array('items')),
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
}
