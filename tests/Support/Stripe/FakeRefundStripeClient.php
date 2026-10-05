<?php

namespace Tests\Support\Stripe;

use JMac\Testing\Double;
use Stripe\Exception\InvalidRequestException;
use Stripe\Service\RefundService;
use Stripe\StripeClient;

/**
 * A Stripe client whose refunds service is a test double, bound into the
 * container so RefundStripePayment uses it.
 */
final class FakeRefundStripeClient extends StripeClient
{
    public function __construct(public RefundService $refunds) {}

    /** Binds a client whose refund call succeeds; verify the returned double to assert it ran once. */
    public static function succeeding(string $refundId = 're_test_fake'): RefundService
    {
        $service = Double::for(RefundService::class);
        $service->expects('create')->returns((object) ['id' => $refundId]);

        self::bind($service);

        return $service;
    }

    /** Binds a client whose refund call is refused by Stripe. */
    public static function refusing(string $message = 'Charge has already been refunded.'): RefundService
    {
        $service = Double::for(RefundService::class);
        $service->expects('create')->throws(InvalidRequestException::factory($message, 400, null, null));

        self::bind($service);

        return $service;
    }

    /** Binds a client that must never be asked to refund; call unused() on the returned double to assert it. */
    public static function untouched(): RefundService
    {
        $service = Double::for(RefundService::class);

        self::bind($service);

        return $service;
    }

    private static function bind(RefundService $service): void
    {
        app()->bind(StripeClient::class, fn (): StripeClient => new self($service));
    }
}
