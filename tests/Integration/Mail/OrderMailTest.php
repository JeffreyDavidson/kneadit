<?php

use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Envelope;

pest()->use(RefreshDatabase::class);

/** @param array<int, mixed> $arguments */
function orderMailEnvelope(string $mailClass, array $arguments): Envelope
{
    $mail = new $mailClass(...$arguments);

    return match (true) {
        $mail instanceof OrderPlacedMail,
        $mail instanceof OrderStatusMail,
        $mail instanceof NewOrderNotificationMail => $mail->envelope(),
        default => throw new RuntimeException('Expected a supported order mailable.'),
    };
}

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Test Bakery',
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
    ]);
    test()->order = Order::factory()->create();
});

test('order mailables have correct envelope subjects', function (string $mailClass, array $args, string $expectedSubjectFragment) {
    $subject = orderMailEnvelope($mailClass, $args)->subject;

    expect($subject)->toContain($expectedSubjectFragment);
})->with([
    'OrderPlacedMail' => [OrderPlacedMail::class, fn () => [test()->order], 'Received — Test Bakery'],
    'OrderStatusMail (Confirmed)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Confirmed], 'Confirmed — Test Bakery'],
    'OrderStatusMail (Ready)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Ready], 'is Ready!'],
    'OrderStatusMail (Baking)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Baking], 'is Being Prepared'],
    'OrderStatusMail (Delivered)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Delivered], 'Delivered'],
    'OrderStatusMail (Cancelled)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Cancelled], 'Cancelled'],
    'NewOrderNotificationMail' => [NewOrderNotificationMail::class, fn () => [test()->order], 'New Order #'],
]);
