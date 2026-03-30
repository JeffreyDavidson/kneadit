<?php

use App\Mail\OrderDeliveredMail;
use App\Mail\RepeatOrderReminderMail;
use App\Mail\WelcomeBakerMail;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('OrderDeliveredMail renders', function () {
    $order = Order::factory()->create();
    $order->load('customer', 'orderItems');

    expect((new OrderDeliveredMail($order))->render())->toBeString()->not->toBeEmpty();
});

test('RepeatOrderReminderMail renders', function () {
    $customer = Customer::factory()->create();

    expect((new RepeatOrderReminderMail($customer, 30))->render())->toBeString()->not->toBeEmpty();
});

test('WelcomeBakerMail renders', function () {
    expect((new WelcomeBakerMail('Jane', 'Sweet Treats', 'https://example.com/admin', 'starter', 'https://example.com/getting-started'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('order-based mail classes render without errors', function (string $mailClass) {
    $order = Order::factory()->create();
    $order->load('customer', 'orderItems.product');

    $html = (new $mailClass($order))->render();

    expect($html)->toBeString()->not->toBeEmpty();
})->with([
    'NewOrderNotification' => [App\Mail\NewOrderNotificationMail::class],
    'OrderPlaced' => [App\Mail\OrderPlacedMail::class],
    'OrderConfirmed' => [App\Mail\OrderConfirmedMail::class],
    'OrderReady' => [App\Mail\OrderReadyMail::class],
    'ReviewRequest' => [App\Mail\ReviewRequestMail::class],
]);

test('StaffInvitationMail renders', function () {
    $invitation = App\Models\StaffInvitation::factory()->create();

    expect((new App\Mail\StaffInvitationMail($invitation, 'Sweet Bakery', 'https://example.com/accept'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('CustomerBlastMail renders', function () {
    expect((new App\Mail\CustomerBlastMail('Sale this weekend!', '<p>50% off all cakes</p>'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('NewSubscriberNotificationMail renders', function () {
    expect((new App\Mail\NewSubscriberNotificationMail('Jane', 'jane@example.com', 'Sweet Bakery', 'sweet-bakery', 'starter'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('BirthdayDiscountMail renders', function () {
    $customer = Customer::factory()->create();
    $coupon = App\Models\Coupon::factory()->percentage()->create();

    expect((new App\Mail\BirthdayDiscountMail($customer, $coupon))->render())
        ->toBeString()->not->toBeEmpty();
});

test('HappyBirthdayMail renders', function () {
    $customer = Customer::factory()->create();

    expect((new App\Mail\HappyBirthdayMail($customer))->render())
        ->toBeString()->not->toBeEmpty();
});

test('ProductAvailableMail renders', function () {
    $product = App\Models\Product::factory()->create();

    expect((new App\Mail\ProductAvailableMail($product, 'Jane'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('PurchaseOrderMail renders', function () {
    $items = [['name' => 'Flour', 'sku' => 'FL-001', 'needed' => 10, 'unit' => 'kg', 'unit_price' => 5.00, 'subtotal' => 50.00]];

    expect((new App\Mail\PurchaseOrderMail('Acme Supplies', 'Sweet Bakery', $items, 100.00, '2026-04-15'))->render())
        ->toBeString()->not->toBeEmpty();
});
