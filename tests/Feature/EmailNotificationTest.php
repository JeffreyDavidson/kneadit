<?php

use App\Mail\OrderBaking;
use App\Mail\OrderConfirmed;
use App\Mail\OrderReady;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    $this->user = User::create(['name' => 'Test', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
    $this->customer = Customer::create(['name' => 'Test Customer', 'email' => 'customer@test.com']);
    $this->order = Order::create([
        'user_id' => $this->user->id,
        'customer_id' => $this->customer->id,
        'status' => 'pending',
        'total' => 25.00,
        'subtotal' => 25.00,
    ]);
    Setting::set('loyalty_enabled', '0');
});

test('order confirmed email sent on status change', function () {
    Mail::fake();

    $this->order->update(['status' => 'confirmed']);

    Mail::assertQueued(OrderConfirmed::class, fn ($mail) => $mail->hasTo('customer@test.com'));
});

test('order ready email sent on status change', function () {
    Mail::fake();

    $this->order->update(['status' => 'ready']);

    Mail::assertQueued(OrderReady::class, fn ($mail) => $mail->hasTo('customer@test.com'));
});

test('baking status sends baking email', function () {
    Mail::fake();

    $this->order->update(['status' => 'baking']);

    Mail::assertQueued(OrderBaking::class);
    Mail::assertNotQueued(OrderConfirmed::class);
    Mail::assertNotQueued(OrderReady::class);
});

test('no email sent when non status field changes', function () {
    Mail::fake();

    $this->order->update(['notes' => 'Updated notes']);

    Mail::assertNothingQueued();
});

test('email contains correct order details', function () {
    $mail = new OrderConfirmed($this->order);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain($this->order->order_number);
});

test('email contains store name from settings', function () {
    Setting::set('store_name', 'Sweet Sunrise Bakery');

    $mail = new OrderConfirmed($this->order);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Sweet Sunrise Bakery');
});

test('email uses default store name when not set', function () {
    $mail = new OrderConfirmed($this->order);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('KneadIt Bakery');
});
