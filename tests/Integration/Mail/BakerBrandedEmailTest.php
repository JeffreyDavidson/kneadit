<?php

use App\Enums\Orders\PaymentMethod;
use App\Mail\Concerns\BakerBranded;
use App\Mail\Customers\HappyBirthdayMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Marketing\CateringQuoteMail;
use App\Mail\Marketing\CustomerBlastMail;
use App\Mail\Orders\NewOrderMessageMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Mail\Platform\WeeklyDigestMail;
use App\Models\Orders\Order;
use App\Models\Staff\User;

beforeEach(function () {
    setUpCentralTest();

    $user = User::factory()->owner()->create();

    test()->order = Order::factory()->recycle($user)->create([
        'payment_method' => PaymentMethod::Cash,
        'delivery_fee' => 0,
        'discount_amount' => 0,
    ]);
});

test('order placed email has baker branded from', function () {
    settings(['store_name' => 'Sweet Treats']);

    $mail = new OrderPlacedMail(testFixture('order', Order::class));
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Sweet Treats via KneadIt');
});

test('order placed email sends from platform domain', function () {
    $mail = new OrderPlacedMail(testFixture('order', Order::class));
    $envelope = $mail->envelope();

    expect($envelope->from->address)->toBe(config('mail.from.address', 'hello@getkneadit.app'));
});

test('order placed email has reply to baker', function () {
    settings(['store_email' => 'baker@sweetreats.com']);
    settings(['store_name' => 'Sweet Treats']);

    $mail = new OrderPlacedMail(testFixture('order', Order::class));
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->not->toBeEmpty()->and($envelope->replyTo[0]->address)->toBe('baker@sweetreats.com');
});

test('reply to is empty when no store email', function () {
    $mail = new OrderPlacedMail(testFixture('order', Order::class));
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->toBeEmpty();
});

test('from name defaults when no store name', function () {
    $mail = new OrderPlacedMail(testFixture('order', Order::class));
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Our Bakery via KneadIt');
});

test('order confirmed uses baker branded from', function () {
    settings(['store_name' => 'Flour Power']);

    $mail = new OrderStatusMail(testFixture('order', Order::class), App\Enums\Orders\OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Flour Power via KneadIt');
});

test('all customer mailables use baker branded trait', function () {
    $mailables = [
        OrderPlacedMail::class,
        OrderStatusMail::class,
        ReviewRequestMail::class,
        HappyBirthdayMail::class,
        CustomerBlastMail::class,
        ProductAvailableMail::class,
        WeeklyDigestMail::class,
        CateringQuoteMail::class,
        NewOrderMessageMail::class,
        RepeatOrderReminderMail::class,
    ];

    foreach ($mailables as $mailable) {
        $uses = class_uses_recursive($mailable);
        expect($uses)->toContain(BakerBranded::class);
    }
});
