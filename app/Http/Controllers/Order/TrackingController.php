<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Show the order tracking page.
     */
    public function show(): View
    {
        return view('order-tracking');
    }

    /**
     * Look up orders by customer email.
     */
    public function store(Request $request): View
    {
        $request->validate(['email' => ['required', 'email']]);

        $orders = Order::whereHas('customer', function (Builder $q) use ($request) {
            $q->where('email', $request->email);
        })
            ->with(['orderItems.product', 'messages'])
            ->latest()
            ->limit(50)
            ->get();

        return view('order-tracking', [
            'orders' => $orders,
            'email' => $request->email,
        ]);
    }
}
