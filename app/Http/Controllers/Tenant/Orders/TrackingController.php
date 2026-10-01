<?php

namespace App\Http\Controllers\Tenant\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\TrackOrderRequest;
use App\Mail\Orders\OrderTrackingLinkMail;
use App\Models\Customers\Customer;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class TrackingController extends Controller
{
    private const string DEFAULT_LINK_SENT_MESSAGE = "If we have orders for that email, we've sent you a link to view them.";

    private const int LINK_COOLDOWN_SECONDS = 300;

    public function show(TenantSettings $settings): View
    {
        return view('tenant.storefront.order-tracking', [
            'settings' => $settings,
            'storefrontTheme' => $settings->branding->storefrontTheme,
            'content' => settingsPageContent('order_tracking'),
        ]);
    }

    /**
     * Email the customer a temporary link to their orders. The response is the
     * same whether or not the address has orders, and no order data is shown.
     */
    public function store(TrackOrderRequest $request): RedirectResponse
    {
        $customer = Customer::query()
            ->where('email', $request->string('email')->toString())
            ->has('orders')
            ->first();

        if ($customer instanceof Customer) {
            RateLimiter::attempt(
                key: "order-tracking-link:{$customer->id}",
                maxAttempts: 1,
                callback: fn () => Mail::to($customer->email)->queue(new OrderTrackingLinkMail(
                    $customer,
                    URL::temporarySignedRoute(
                        'order.track.access',
                        now()->addMinutes(OrderTrackingLinkMail::LINK_LIFETIME_MINUTES),
                        ['customer' => $customer],
                    ),
                )),
                decaySeconds: self::LINK_COOLDOWN_SECONDS,
            );
        }

        return redirect()
            ->route('order.track')
            ->with('status', settingsPageContent('order_tracking')['link_sent_message'] ?? self::DEFAULT_LINK_SENT_MESSAGE);
    }
}
