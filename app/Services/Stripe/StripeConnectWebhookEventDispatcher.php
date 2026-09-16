<?php

namespace App\Services\Stripe;

use App\Actions\Stripe\HandleConnectAccountUpdated;
use App\Actions\Stripe\HandleConnectCheckoutCompleted;

class StripeConnectWebhookEventDispatcher
{
    public function __construct(
        private readonly HandleConnectAccountUpdated $handleAccountUpdated,
        private readonly HandleConnectCheckoutCompleted $handleCheckoutCompleted,
    ) {}

    public function dispatch(string $type, mixed $data): void
    {
        if ($type === 'account.updated') {
            ($this->handleAccountUpdated)($data);

            return;
        }

        if ($type === 'checkout.session.completed') {
            ($this->handleCheckoutCompleted)($data);
        }
    }
}
