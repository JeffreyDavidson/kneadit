<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use Illuminate\Http\RedirectResponse;

class MarkOrderDeliveredController extends Controller
{
    public function __invoke(Order $order, TransitionOrderStatus $transitionStatus): RedirectResponse
    {
        $transitionStatus($order, OrderStatus::Delivered);

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
