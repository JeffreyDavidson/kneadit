<?php

namespace App\Http\Controllers\Tenant\Storefront;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use Illuminate\Http\RedirectResponse;

class MarkOrderDeliveredController extends Controller
{
    public function __invoke(Order $order, TransitionOrderStatus $transitionStatus): RedirectResponse
    {
        try {
            $transitionStatus($order, OrderStatus::Delivered);
        } catch (InvalidOrderTransitionException) {
            return back()->with('error', "Order #{$order->order_number} can't be marked as delivered because it is {$order->status->value}, not ready.");
        }

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
