<?php

use App\Http\Controllers\StripeWebhookController;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
    Mail::fake();
});

test('handleInvoicePaymentFailed sends email to baker', function () {
    $user = User::factory()->owner()->create(['stripe_id' => 'cus_test123']);
    Tenant::factory()->create(['email' => $user->email]);

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $method->invoke($controller, [
        'id' => 'evt_test_' . uniqid(),
        'data' => [
            'object' => [
                'customer' => 'cus_test123',
                'amount_due' => 2900,
            ],
        ],
    ]);

    Mail::assertQueued(App\Mail\PaymentFailedMail::class, fn ($mail) => $mail->hasTo($user->email));
});

test('duplicate events are skipped via idempotency check', function () {
    $user = User::factory()->owner()->create(['stripe_id' => 'cus_test456']);
    Tenant::factory()->create(['email' => $user->email]);

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'handleInvoicePaymentFailed');

    $eventId = 'evt_duplicate_' . uniqid();
    $payload = [
        'id' => $eventId,
        'data' => ['object' => ['customer' => 'cus_test456', 'amount_due' => 1000]],
    ];

    $method->invoke($controller, $payload);
    Mail::assertQueued(App\Mail\PaymentFailedMail::class);

    Mail::fake(); // Reset
    $method->invoke($controller, $payload);
    Mail::assertNothingQueued();
});

test('priceIdToPlan maps stripe price to plan name', function () {
    config(['kneadit.stripe_prices' => ['starter' => 'price_starter', 'growth' => 'price_growth']]);

    $controller = new StripeWebhookController;
    $method = new ReflectionMethod($controller, 'priceIdToPlan');

    expect($method->invoke($controller, 'price_starter'))->toBe('starter')
        ->and($method->invoke($controller, 'price_growth'))->toBe('growth')
        ->and($method->invoke($controller, 'price_unknown'))->toBeNull();
});
