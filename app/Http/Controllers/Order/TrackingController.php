<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

class TrackingController extends Controller
{
    /**
     * Show the order tracking page.
     */
    public function show(): View
    {
        $content = settingsPageContent('order_tracking');
        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

        return view('order-tracking', [
            'content' => $content,
            'storeName' => $storeName,
            'heroImageUrl' => $heroImageUrl,
        ]);
    }

    /**
     * Look up orders by customer email.
     */
    public function store(TrackOrderRequest $request): View
    {
        $orders = Order::query()->forCustomerEmail($request->email)->get();

        $content = settingsPageContent('order_tracking');
        $storeName = settings('store_name', 'Our Bakery');
        $heroImage = settings('hero_image');
        $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';

        $allStatuses = [
            OrderStatus::Pending->value,
            OrderStatus::Confirmed->value,
            OrderStatus::Baking->value,
            OrderStatus::Ready->value,
            OrderStatus::Delivered->value,
        ];
        $statusLabels = [
            OrderStatus::Pending->value => 'Pending',
            OrderStatus::Confirmed->value => 'Confirmed',
            OrderStatus::Baking->value => 'Baking',
            OrderStatus::Ready->value => 'Ready',
            OrderStatus::Delivered->value => 'Delivered',
        ];

        return view('order-tracking', [
            'orders' => $orders,
            'email' => $request->email,
            'content' => $content,
            'storeName' => $storeName,
            'heroImageUrl' => $heroImageUrl,
            'allStatuses' => $allStatuses,
            'statusLabels' => $statusLabels,
        ]);
    }
}
