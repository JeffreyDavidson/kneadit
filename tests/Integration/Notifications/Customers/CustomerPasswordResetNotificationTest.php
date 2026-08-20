<?php

use App\Models\Customers\Customer;
use App\Notifications\Customers\CustomerPasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings(['store_name' => 'Sweet Treats Bakery']);
});

test('subject includes the store name', function () {
    $customer = Customer::factory()->create();

    $message = (new CustomerPasswordResetNotification('reset-token'))->toMail($customer);

    expect($message->subject)->toBe('Reset your password — Sweet Treats Bakery');
});

test('body mentions the store name', function () {
    $customer = Customer::factory()->create();

    $message = (new CustomerPasswordResetNotification('reset-token'))->toMail($customer);

    expect(collect($message->introLines)->implode("\n"))->toContain('Sweet Treats Bakery');
});

test('action button points at the password reset route with the token', function () {
    $customer = Customer::factory()->create(['email' => 'jane@example.com']);

    $message = (new CustomerPasswordResetNotification('the-token'))->toMail($customer);

    expect($message->actionUrl)
        ->toContain('the-token')
        ->and($message->actionUrl)->toContain('jane%40example.com');
});
