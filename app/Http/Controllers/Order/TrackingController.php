<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use Illuminate\Contracts\Database\Query\Builder;
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

        return view('order-tracking', compact('content', 'storeName', 'heroImageUrl'));
    }

    /**
     * Look up orders by customer email.
     */
    public function store(TrackOrderRequest $request): View
    {

        $orders = Order::query()->whereHas('customer', function (Builder $q) use ($request) {
            $q->where('email', $request->email);
        })
            ->with(['customer', 'orderItems.product', 'messages'])
            ->latest()
            ->limit(50)
            ->get();

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
