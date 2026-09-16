<?php

use App\Actions\Stripe\HandleConnectAccountUpdated;
use App\Actions\Stripe\HandleConnectCheckoutCompleted;
use App\Services\Stripe\StripeConnectWebhookEventDispatcher;

test('dispatches account updated events to the account action', function () {
    $data = (object) ['id' => 'acct_123'];
    $accountAction = Mockery::mock(HandleConnectAccountUpdated::class);
    $accountAction->shouldReceive('__invoke')->once()->with($data);

    $checkoutAction = Mockery::mock(HandleConnectCheckoutCompleted::class);
    $checkoutAction->shouldNotReceive('__invoke');

    $dispatcher = new StripeConnectWebhookEventDispatcher($accountAction, $checkoutAction);

    $dispatcher->dispatch('account.updated', $data);
});

test('dispatches completed checkout events to the checkout action', function () {
    $data = (object) ['id' => 'cs_123'];
    $accountAction = Mockery::mock(HandleConnectAccountUpdated::class);
    $accountAction->shouldNotReceive('__invoke');

    $checkoutAction = Mockery::mock(HandleConnectCheckoutCompleted::class);
    $checkoutAction->shouldReceive('__invoke')->once()->with($data);

    $dispatcher = new StripeConnectWebhookEventDispatcher($accountAction, $checkoutAction);

    $dispatcher->dispatch('checkout.session.completed', $data);
});

test('ignores unsupported event types', function () {
    $accountAction = Mockery::mock(HandleConnectAccountUpdated::class);
    $accountAction->shouldNotReceive('__invoke');

    $checkoutAction = Mockery::mock(HandleConnectCheckoutCompleted::class);
    $checkoutAction->shouldNotReceive('__invoke');

    $dispatcher = new StripeConnectWebhookEventDispatcher($accountAction, $checkoutAction);

    $dispatcher->dispatch('unsupported.event', (object) []);
});
