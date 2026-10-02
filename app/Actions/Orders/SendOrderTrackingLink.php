<?php

namespace App\Actions\Orders;

use App\Mail\Orders\OrderTrackingLinkMail;
use App\Models\Customers\Customer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

/**
 * Email a temporary signed link to a customer's orders. Does nothing when the
 * address has no orders, and sends at most one mail per customer per cooldown.
 */
class SendOrderTrackingLink
{
    private const int COOLDOWN_SECONDS = 300;

    public function __invoke(string $email): void
    {
        $customer = Customer::query()
            ->forEmail($email)
            ->has('orders')
            ->first();

        if (! $customer instanceof Customer) {
            return;
        }

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
            decaySeconds: self::COOLDOWN_SECONDS,
        );
    }
}
