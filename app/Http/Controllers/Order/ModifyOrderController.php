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
                $request->validated('items'),
                $request->validated('tip_amount') !== null
                    ? (float) $request->validated('tip_amount')
                    : null,
            );
        } catch (OrderNotModifiableException $e) {
            return back()->withErrors(['items' => $e->reason]);
        }

        return to_route('order.confirmation', $order)
            ->with('success', 'Your order was updated.');
    }
}
