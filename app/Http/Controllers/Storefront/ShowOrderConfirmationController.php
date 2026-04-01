<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowOrderConfirmationController extends Controller
{
    public function __invoke(Order $order, TenantSettings $settings): View
    {
        $order->load('orderItems.product');

        $content = settingsPageContent('order_confirmation');

        return view('storefront.order-confirmation', [
            'settings' => $settings,
            'order' => $order,
            'content' => $content,
            'journeySteps' => $content['journey_steps'] ?? config('kneadit.default_journey_steps'),
        ]);
    }
}
