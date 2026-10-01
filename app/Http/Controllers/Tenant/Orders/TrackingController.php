<?php

namespace App\Http\Controllers\Tenant\Orders;

use App\Actions\Orders\SendOrderTrackingLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\TrackOrderRequest;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TrackingController extends Controller
{
    private const string DEFAULT_LINK_SENT_MESSAGE = "If we have orders for that email, we've sent you a link to view them.";

    /**
     * Email the customer a temporary link to their orders. The response is the
     * same whether or not the address has orders, and no order data is shown.
     */
    public function store(TrackOrderRequest $request, SendOrderTrackingLink $sendTrackingLink): RedirectResponse
    {
        $sendTrackingLink($request->string('email')->toString());

        return redirect()
            ->route('order.track')
            ->with('status', settingsPageContent('order_tracking')['link_sent_message'] ?? self::DEFAULT_LINK_SENT_MESSAGE);
    }

    public function show(TenantSettings $settings): View
    {
        return view('tenant.storefront.order-tracking', [
            'settings' => $settings,
            'storefrontTheme' => $settings->branding->storefrontTheme,
            'content' => settingsPageContent('order_tracking'),
        ]);
    }
}
