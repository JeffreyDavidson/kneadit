<?php

use App\Models\Customers\Customer;
use App\Notifications\Customers\CustomerVerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['store_name' => 'Sweet Treats Bakery']);
});

test('subject includes the store name', function () {
    $customer = Customer::factory()->create();

    $message = (new CustomerVerifyEmailNotification)->toMail($customer);

    expect($message->subject)->toBe('Verify your email — Sweet Treats Bakery');
});

test('body mentions the store name', function () {
    $customer = Customer::factory()->create();

    $message = (new CustomerVerifyEmailNotification)->toMail($customer);

    expect(implode("\n", $message->introLines))->toContain('Sweet Treats Bakery');
});

test('action url is a signed verification route', function () {
    $customer = Customer::factory()->create();

    $message = (new CustomerVerifyEmailNotification)->toMail($customer);

    expect($message->actionUrl)
        ->toContain('signature=')
        ->and($message->actionUrl)->toContain('expires=');
});
