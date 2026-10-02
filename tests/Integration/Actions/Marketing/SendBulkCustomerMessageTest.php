<?php

use App\Actions\Marketing\SendBulkCustomerMessage;
use App\Enums\Marketing\BulkMessagePurpose;
use App\Mail\Customers\BulkCustomerMessageMail;
use App\Mail\Customers\OrderUpdateMessageMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('returns zero when given an empty collection', function () {
    $outcome = resolve(SendBulkCustomerMessage::class)(collect(), BulkMessagePurpose::Promotion, 'x', 'y');

    expect($outcome->sent)->toBe(0)
        ->and($outcome->skippedNoEmail)->toBe(0)
        ->and($outcome->skippedIneligible)->toBe(0);
    Mail::assertNothingQueued();
});

test('queues one mailable per customer with the given subject and body', function () {
    $alice = Customer::factory()->create(['email' => 'alice@example.com', 'name' => 'Alice']);
    $bob = Customer::factory()->create(['email' => 'bob@example.com', 'name' => 'Bob']);

    $outcome = resolve(SendBulkCustomerMessage::class)(
        collect([$alice, $bob]),
        BulkMessagePurpose::Promotion,
        messageSubject: 'Heads up — pickup window changed',
        body: 'Your pickup is now between 3pm and 5pm tomorrow.',
    );

    expect($outcome->sent)->toBe(2);
    Mail::assertQueued(BulkCustomerMessageMail::class, 2);
    Mail::assertQueued(BulkCustomerMessageMail::class, fn (BulkCustomerMessageMail $m) => $m->hasTo('alice@example.com')
        && $m->messageSubject === 'Heads up — pickup window changed'
        && $m->body === 'Your pickup is now between 3pm and 5pm tomorrow.');
    Mail::assertQueued(BulkCustomerMessageMail::class, fn (BulkCustomerMessageMail $m) => $m->hasTo('bob@example.com'));
});

test('mailable rendering preserves line breaks and greets the customer by name', function () {
    settings(['store_name' => 'Test Bakery']);
    $customer = Customer::factory()->create(['email' => 'alice@example.com', 'name' => 'Alice']);
    $mail = new BulkCustomerMessageMail($customer, 'Subject', "First line.\nSecond line.");

    $rendered = $mail->render();

    expect($rendered)
        ->toContain('Hi Alice,')
        ->toContain('First line.<br />')
        ->toContain('Second line.');
});

test('order update goes to opted-out customers with an open order and skips the rest', function () {
    $optedOut = Customer::factory()->unsubscribed()->has(Order::factory()->baking())->create();
    $subscribed = Customer::factory()->has(Order::factory()->pending())->create();
    $noOpenOrder = Customer::factory()->has(Order::factory()->delivered())->create();
    $noOrders = Customer::factory()->create();
    $noEmail = Customer::factory()->has(Order::factory()->ready())->create(['email' => '']);

    $outcome = resolve(SendBulkCustomerMessage::class)(
        [$optedOut, $subscribed, $noOpenOrder, $noOrders, $noEmail],
        BulkMessagePurpose::OrderUpdate,
        'Pickup window changed',
        'Pickup is now 3pm to 5pm.',
    );

    expect($outcome->sent)->toBe(2)
        ->and($outcome->skippedIneligible)->toBe(2)
        ->and($outcome->skippedNoEmail)->toBe(1);
    Mail::assertQueued(OrderUpdateMessageMail::class, 2);
    Mail::assertQueued(OrderUpdateMessageMail::class, fn (OrderUpdateMessageMail $m) => $m->hasTo($optedOut->email));
    Mail::assertNotQueued(BulkCustomerMessageMail::class);
});

test('promotion skips opted-out customers and customers without an email', function () {
    $subscribed = Customer::factory()->create();
    $optedOut = Customer::factory()->unsubscribed()->create();
    $noEmail = Customer::factory()->create(['email' => '']);

    $outcome = resolve(SendBulkCustomerMessage::class)(
        [$subscribed, $optedOut, $noEmail],
        BulkMessagePurpose::Promotion,
        'Sale',
        'Half price bread.',
    );

    expect($outcome->sent)->toBe(1)
        ->and($outcome->skippedIneligible)->toBe(1)
        ->and($outcome->skippedNoEmail)->toBe(1);
    Mail::assertQueued(BulkCustomerMessageMail::class, 1);
    Mail::assertNotQueued(OrderUpdateMessageMail::class);
});
