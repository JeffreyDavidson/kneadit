<?php

use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('Ready email CCs the pickup contact when set', function () {
    $order = Order::factory()
        ->for(Customer::factory()->create())
        ->create([
            'pickup_contact_name' => 'Bob',
            'pickup_contact_email' => 'bob@example.com',
        ]);

    $cc = (new OrderStatusMail($order, OrderStatus::Ready))->envelope()->cc;

    expect($cc)->toHaveCount(1)
        ->and($cc[0]->address)->toBe('bob@example.com')
        ->and($cc[0]->name)->toBe('Bob');
});

test('Ready email has no CC when pickup contact is not set', function () {
    $order = Order::factory()
        ->for(Customer::factory()->create())
        ->create();

    expect((new OrderStatusMail($order, OrderStatus::Ready))->envelope()->cc)->toBeEmpty();
});

test('Baking email does not CC the pickup contact even when set', function () {
    $order = Order::factory()
        ->for(Customer::factory()->create())
        ->create([
            'pickup_contact_name' => 'Bob',
            'pickup_contact_email' => 'bob@example.com',
        ]);

    expect((new OrderStatusMail($order, OrderStatus::Baking))->envelope()->cc)->toBeEmpty();
});

test('falls back to email as the name when pickup_contact_name is null', function () {
    $order = Order::factory()
        ->for(Customer::factory()->create())
        ->create([
            'pickup_contact_name' => null,
            'pickup_contact_email' => 'bob@example.com',
        ]);

    $cc = (new OrderStatusMail($order, OrderStatus::Ready))->envelope()->cc;

    expect($cc[0]->name)->toBe('bob@example.com');
});
