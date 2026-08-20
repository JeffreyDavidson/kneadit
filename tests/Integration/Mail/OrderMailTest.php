<?php

use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

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
    $mail = new $mailClass(...$args);
    $subject = $mail->envelope()->subject;

    expect($subject)->toContain($expectedSubjectFragment);
})->with([
    'OrderPlacedMail' => [OrderPlacedMail::class, fn () => [testFixture('order', Order::class)], 'Received — Test Bakery'],
    'OrderStatusMail (Confirmed)' => [OrderStatusMail::class, fn () => [testFixture('order', Order::class), OrderStatus::Confirmed], 'Confirmed — Test Bakery'],
    'OrderStatusMail (Ready)' => [OrderStatusMail::class, fn () => [testFixture('order', Order::class), OrderStatus::Ready], 'is Ready!'],
    'OrderStatusMail (Baking)' => [OrderStatusMail::class, fn () => [testFixture('order', Order::class), OrderStatus::Baking], 'is Being Prepared'],
    'OrderStatusMail (Delivered)' => [OrderStatusMail::class, fn () => [testFixture('order', Order::class), OrderStatus::Delivered], 'Delivered'],
    'OrderStatusMail (Cancelled)' => [OrderStatusMail::class, fn () => [testFixture('order', Order::class), OrderStatus::Cancelled], 'Cancelled'],
    'NewOrderNotificationMail' => [NewOrderNotificationMail::class, fn () => [testFixture('order', Order::class)], 'New Order #'],
]);
