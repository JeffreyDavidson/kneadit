<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
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

    $this->user = User::query()->create(['name' => 'Test', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
    $this->customer = Customer::query()->create(['name' => 'Test Customer', 'email' => 'customer@test.com']);
    $this->order = Order::query()->create([
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

    resolve(TransitionOrderStatus::class)($this->order, OrderStatus::Confirmed);

    Mail::assertQueued(OrderConfirmed::class, fn ($mail) => $mail->hasTo('customer@test.com'));
});

test('order ready email sent on status change', function () {
    Mail::fake();

    $this->order->update(['status' => 'confirmed']);
    resolve(TransitionOrderStatus::class)($this->order->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)($this->order->fresh(), OrderStatus::Ready);

    Mail::assertQueued(OrderReady::class, fn ($mail) => $mail->hasTo('customer@test.com'));
});

test('baking status sends baking email', function () {
    Mail::fake();

    resolve(TransitionOrderStatus::class)($this->order, OrderStatus::Confirmed);
    Mail::fake(); // Reset to only capture baking email
    resolve(TransitionOrderStatus::class)($this->order->fresh(), OrderStatus::Baking);

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
