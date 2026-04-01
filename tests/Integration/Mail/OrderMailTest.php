<?php

use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderBakingMail;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderDeliveredMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderReadyMail;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Test Bakery',
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
    ]);
    test()->order = Order::factory()->create();
});

test('order mailables have correct envelope subjects', function (string $mailClass, string $expectedSubjectFragment) {
    $mail = new $mailClass(test()->order);
    $subject = $mail->envelope()->subject;

    expect($subject)->toContain($expectedSubjectFragment);
})->with([
    'OrderPlacedMail' => [OrderPlacedMail::class, 'Received — Test Bakery'],
    'OrderConfirmedMail' => [OrderConfirmedMail::class, 'Confirmed — Test Bakery'],
    'OrderReadyMail' => [OrderReadyMail::class, 'is Ready!'],
    'OrderBakingMail' => [OrderBakingMail::class, 'is Being Prepared'],
    'OrderDeliveredMail' => [OrderDeliveredMail::class, 'Delivered'],
    'OrderCancelledMail' => [OrderCancelledMail::class, 'Cancelled'],
    'NewOrderNotificationMail' => [NewOrderNotificationMail::class, 'New Order #'],
]);
