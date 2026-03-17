<?php

use App\Mail\BirthdayDiscount;
use App\Mail\CateringQuote;
use App\Mail\Concerns\BakerBranded;
use App\Mail\CustomerBlast;
use App\Mail\HappyBirthday;
use App\Mail\NewOrderMessage;
use App\Mail\OrderBaking;
use App\Mail\OrderCancelled;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;
use App\Mail\OrderPlaced;
use App\Mail\OrderReady;
use App\Mail\ProductAvailable;
use App\Mail\RepeatOrderReminder;
use App\Mail\ReviewRequest;
use App\Mail\WeeklyDigest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    setUpCentralTest();

    $user = User::create([
        'name' => 'Baker',
        'email' => 'baker@test.com',
        'password' => bcrypt('password'),
    ]);

    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'customer@test.com',
    ]);

    $this->order = Order::create([
        'order_number' => 'ORD-001',
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'status' => 'pending',
        'payment_status' => 'unpaid',
        'payment_method' => 'cash',
        'subtotal' => 25.00,
        'delivery_fee' => 0,
        'discount_amount' => 0,
        'total' => 25.00,
    ]);
});

test('order placed email has baker branded from', function () {
    Setting::set('store_name', 'Sweet Treats');

    $mail = new OrderPlaced($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Sweet Treats via KneadIt');
});

test('order placed email sends from platform domain', function () {
    $mail = new OrderPlaced($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->address)->toBe(config('mail.from.address', 'hello@getkneadit.app'));
});

test('order placed email has reply to baker', function () {
    Setting::set('store_email', 'baker@sweetreats.com');
    Setting::set('store_name', 'Sweet Treats');

    $mail = new OrderPlaced($this->order);
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->not->toBeEmpty();
    expect($envelope->replyTo[0]->address)->toBe('baker@sweetreats.com');
});

test('reply to is empty when no store email', function () {
    $mail = new OrderPlaced($this->order);
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->toBeEmpty();
});

test('from name defaults when no store name', function () {
    $mail = new OrderPlaced($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('KneadIt Bakery via KneadIt');
});

test('order confirmed uses baker branded from', function () {
    Setting::set('store_name', 'Flour Power');

    $mail = new OrderConfirmed($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Flour Power via KneadIt');
});

test('all customer mailables use baker branded trait', function () {
    $mailables = [
        OrderPlaced::class,
        OrderConfirmed::class,
        OrderReady::class,
        OrderBaking::class,
        OrderCancelled::class,
        OrderDelivered::class,
        ReviewRequest::class,
        HappyBirthday::class,
        BirthdayDiscount::class,
        CustomerBlast::class,
        ProductAvailable::class,
        WeeklyDigest::class,
        CateringQuote::class,
        NewOrderMessage::class,
        RepeatOrderReminder::class,
    ];

    foreach ($mailables as $mailable) {
        $uses = class_uses_recursive($mailable);
        expect($uses)->toContain(BakerBranded::class, "{$mailable} should use BakerBranded trait");
    }
});
