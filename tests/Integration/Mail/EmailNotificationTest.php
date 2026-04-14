<?php

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    setUpTenantTest();

    $this->user = User::factory()->owner()->create();
    $this->customer = Customer::factory()->create();
    $this->order = Order::factory()
        ->for($this->customer)
        ->recycle($this->user)
        ->create(['total' => 25.00, 'subtotal' => 25.00]);
    settings(['loyalty_enabled' => '0']);
});

test('order confirmed email sent on status change', function () {
    Mail::fake();

    resolve(TransitionOrderStatus::class)($this->order, OrderStatus::Confirmed);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Confirmed && $mail->hasTo($this->customer->email));
});

test('order ready email sent on status change', function () {
    Mail::fake();

    $this->order->update(['status' => OrderStatus::Confirmed]);
    resolve(TransitionOrderStatus::class)($this->order->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)($this->order->fresh(), OrderStatus::Ready);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Ready && $mail->hasTo($this->customer->email));
});

test('baking status sends baking email', function () {
    Mail::fake();

    resolve(TransitionOrderStatus::class)($this->order, OrderStatus::Confirmed);
    Mail::fake(); // Reset to only capture baking email
    resolve(TransitionOrderStatus::class)($this->order->fresh(), OrderStatus::Baking);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Baking);
    Mail::assertNotQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Confirmed);
    Mail::assertNotQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Ready);
});

test('no email sent when non status field changes', function () {
    Mail::fake();

    $this->order->update(['notes' => 'Updated notes']);

    Mail::assertNothingQueued();
});

test('email contains correct order details', function () {
    $mail = new OrderStatusMail($this->order, OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain($this->order->order_number);
});

test('email contains store name from settings', function () {
    settings(['store_name' => 'Sweet Sunrise Bakery']);

    $mail = new OrderStatusMail($this->order, OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Sweet Sunrise Bakery');
});

test('email uses default store name when not set', function () {
    $mail = new OrderStatusMail($this->order, OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Our Bakery');
});
