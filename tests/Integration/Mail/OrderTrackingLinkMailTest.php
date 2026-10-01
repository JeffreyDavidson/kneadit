<?php

use App\Mail\Orders\OrderTrackingLinkMail;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['store_name' => 'Test Bakery']);
});

test('the envelope subject names the bakery', function () {
    $mail = new OrderTrackingLinkMail(Customer::factory()->make(), 'https://shop.example.com/track/access/1');

    expect($mail->envelope()->subject)->toBe('Your order link — Test Bakery');
});

test('the body contains the link and its expiry', function () {
    $customer = Customer::factory()->make(['name' => 'Jane Doe']);
    $mail = new OrderTrackingLinkMail($customer, 'https://shop.example.com/track/access/1?signature=abc');

    $html = $mail->render();

    expect($html)
        ->toContain('https://shop.example.com/track/access/1?signature=abc')
        ->toContain('30 minutes')
        ->toContain('Jane');
});
