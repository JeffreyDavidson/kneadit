<?php

use App\Mail\OrderDeliveredMail;
use App\Mail\RepeatOrderReminderMail;
use App\Mail\WelcomeBakerMail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('OrderDeliveredMail renders', function () {
    $order = Order::factory()->create();
    $order->load('customer', 'orderItems');

    expect((new OrderDeliveredMail($order))->render())->toBeString()->not->toBeEmpty();
});

test('RepeatOrderReminderMail renders', function () {
    $customer = Customer::factory()->create();

    expect((new RepeatOrderReminderMail($customer, 30))->render())->toBeString()->not->toBeEmpty();
});

test('WelcomeBakerMail renders', function () {
    expect((new WelcomeBakerMail('Jane', 'Sweet Treats', 'https://example.com/admin', 'starter', 'https://example.com/getting-started'))->render())
        ->toBeString()->not->toBeEmpty();
});
