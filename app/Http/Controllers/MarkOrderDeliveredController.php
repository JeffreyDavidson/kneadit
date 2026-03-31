<?php

namespace App\Http\Controllers;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;

class MarkOrderDeliveredController extends Controller
{
    public function __invoke(Order $order, TransitionOrderStatus $transitionStatus): RedirectResponse
    {
        $transitionStatus($order, OrderStatus::Delivered);

        return back()->with('success', "Order #{$order->order_number} marked as delivered!");
    }
}
