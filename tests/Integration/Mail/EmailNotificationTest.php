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

    test()->user = User::factory()->owner()->create();
    test()->customer = Customer::factory()->create();
    test()->order = Order::factory()
        ->for(testFixture('customer', Customer::class))
        ->recycle(testFixture('user', User::class))
        ->create(['total' => 25.00, 'subtotal' => 25.00]);
    settings(['loyalty_enabled' => '0']);
});

test('order confirmed email sent on status change', function () {
    Mail::fake();

    resolve(TransitionOrderStatus::class)(testFixture('order', Order::class), OrderStatus::Confirmed);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Confirmed && $mail->hasTo(testFixture('customer', Customer::class)->email));
});

test('order ready email sent on status change', function () {
    Mail::fake();

    testFixture('order', Order::class)->update(['status' => OrderStatus::Confirmed]);
    resolve(TransitionOrderStatus::class)(testFixture('order', Order::class)->fresh(), OrderStatus::Baking);
    resolve(TransitionOrderStatus::class)(testFixture('order', Order::class)->fresh(), OrderStatus::Ready);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Ready && $mail->hasTo(testFixture('customer', Customer::class)->email));
});

test('baking status sends baking email', function () {
    Mail::fake();

    resolve(TransitionOrderStatus::class)(testFixture('order', Order::class), OrderStatus::Confirmed);
    Mail::fake(); // Reset to only capture baking email
    resolve(TransitionOrderStatus::class)(testFixture('order', Order::class)->fresh(), OrderStatus::Baking);

    Mail::assertQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Baking);
    Mail::assertNotQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Confirmed);
    Mail::assertNotQueued(OrderStatusMail::class, fn (OrderStatusMail $mail) => $mail->status === OrderStatus::Ready);
});

test('no email sent when non status field changes', function () {
    Mail::fake();

    testFixture('order', Order::class)->update(['notes' => 'Updated notes']);

    Mail::assertNothingQueued();
});

test('email contains correct order details', function () {
    $mail = new OrderStatusMail(testFixture('order', Order::class), OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain(testFixture('order', Order::class)->order_number);
});

test('email contains store name from settings', function () {
    settings(['store_name' => 'Sweet Sunrise Bakery']);

    $mail = new OrderStatusMail(testFixture('order', Order::class), OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Sweet Sunrise Bakery');
});

test('email uses default store name when not set', function () {
    $mail = new OrderStatusMail(testFixture('order', Order::class), OrderStatus::Confirmed);
    $envelope = $mail->envelope();

    expect($envelope->subject)->toContain('Our Bakery');
});
