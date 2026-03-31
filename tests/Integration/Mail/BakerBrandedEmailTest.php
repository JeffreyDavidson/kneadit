<?php

use App\Enums\PaymentMethod;
use App\Mail\BirthdayDiscountMail;
use App\Mail\CateringQuoteMail;
use App\Mail\Concerns\BakerBranded;
use App\Mail\CustomerBlastMail;
use App\Mail\HappyBirthdayMail;
use App\Mail\NewOrderMessageMail;
use App\Mail\OrderBakingMail;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderReadyMail;
use App\Mail\ProductAvailableMail;
use App\Mail\RepeatOrderReminderMail;
use App\Mail\ReviewRequestMail;
use App\Mail\WeeklyDigestMail;
use App\Models\Order;
use App\Models\User;

beforeEach(function () {
    setUpCentralTest();

    $user = User::factory()->owner()->create();

    $this->order = Order::factory()->recycle($user)->create([
        'payment_method' => PaymentMethod::Cash,
        'delivery_fee' => 0,
        'discount_amount' => 0,
    ]);
});

test('order placed email has baker branded from', function () {
    settings(['store_name' => 'Sweet Treats']);

    $mail = new OrderPlacedMail($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Sweet Treats via KneadIt');
});

test('order placed email sends from platform domain', function () {
    $mail = new OrderPlacedMail($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->address)->toBe(config('mail.from.address', 'hello@getkneadit.app'));
});

test('order placed email has reply to baker', function () {
    settings(['store_email' => 'baker@sweetreats.com']);
    settings(['store_name' => 'Sweet Treats']);

    $mail = new OrderPlacedMail($this->order);
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->not->toBeEmpty()->and($envelope->replyTo[0]->address)->toBe('baker@sweetreats.com');
});

test('reply to is empty when no store email', function () {
    $mail = new OrderPlacedMail($this->order);
    $envelope = $mail->envelope();

    expect($envelope->replyTo)->toBeEmpty();
});

test('from name defaults when no store name', function () {
    $mail = new OrderPlacedMail($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Our Bakery via KneadIt');
});

test('order confirmed uses baker branded from', function () {
    settings(['store_name' => 'Flour Power']);

    $mail = new OrderConfirmedMail($this->order);
    $envelope = $mail->envelope();

    expect($envelope->from->name)->toContain('Flour Power via KneadIt');
});

test('all customer mailables use baker branded trait', function () {
    $mailables = [
        OrderPlacedMail::class,
        OrderConfirmedMail::class,
        OrderReadyMail::class,
        OrderBakingMail::class,
        OrderCancelledMail::class,
        OrderDeliveredMail::class,
        ReviewRequestMail::class,
        HappyBirthdayMail::class,
        BirthdayDiscountMail::class,
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
