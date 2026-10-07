<?php

namespace Tests\Support\Stripe;

use JMac\Testing\Double;
use Stripe\Service\Checkout\SessionService;
use Stripe\StripeClient;

/**
 * A Stripe client whose Checkout sessions service is a test double, bound into
 * the container so the checkout actions and services use it.
 */
final class FakeCheckoutStripeClient extends StripeClient
{
    public object $checkout;

    public function __construct(SessionService $sessions)
    {
        $this->checkout = (object) ['sessions' => $sessions];
    }

    /** Binds a client around a fresh sessions double; set expectations on the returned double. */
    public static function bind(): SessionService
    {
        $sessions = Double::for(SessionService::class);

        app()->bind(StripeClient::class, fn (): StripeClient => new self($sessions));

        return $sessions;
    }
}
