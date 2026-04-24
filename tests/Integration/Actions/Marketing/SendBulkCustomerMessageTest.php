<?php

use App\Actions\Marketing\SendBulkCustomerMessage;
use App\Mail\Customers\BulkCustomerMessageMail;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('returns zero when given an empty collection', function () {
    $sent = resolve(SendBulkCustomerMessage::class)(collect(), 'x', 'y');

    expect($sent)->toBe(0);
    Mail::assertNothingQueued();
});

test('queues one mailable per customer with the given subject and body', function () {
    $alice = Customer::factory()->create(['email' => 'alice@example.com', 'name' => 'Alice']);
    $bob = Customer::factory()->create(['email' => 'bob@example.com', 'name' => 'Bob']);

    $sent = resolve(SendBulkCustomerMessage::class)(
        collect([$alice, $bob]),
        messageSubject: 'Heads up — pickup window changed',
        body: 'Your pickup is now between 3pm and 5pm tomorrow.',
    );

    expect($sent)->toBe(2);
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
