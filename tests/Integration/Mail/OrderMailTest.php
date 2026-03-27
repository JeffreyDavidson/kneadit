<?php

use App\Mail\NewOrderNotification;
use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderPlaced;
use App\Mail\OrderReady;
use App\Models\Order;
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
    'OrderPlaced' => [OrderPlaced::class, 'Received — Test Bakery'],
    'OrderConfirmed' => [OrderConfirmed::class, 'Confirmed — Test Bakery'],
    'OrderReady' => [OrderReady::class, 'is Ready!'],
    'OrderBaking' => [OrderBaking::class, 'is Being Prepared'],
    'OrderDelivered' => [OrderDelivered::class, 'Delivered'],
    'OrderCancelled' => [OrderCancelled::class, 'Cancelled'],
    'NewOrderNotification' => [NewOrderNotification::class, 'New Order #'],
]);
