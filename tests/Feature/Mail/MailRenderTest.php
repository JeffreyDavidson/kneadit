<?php

use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Orders\OrderDeliveredMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
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
    'NewOrderNotification' => [App\Mail\Orders\NewOrderNotificationMail::class],
    'OrderPlaced' => [App\Mail\Orders\OrderPlacedMail::class],
    'OrderConfirmed' => [App\Mail\Orders\OrderConfirmedMail::class],
    'OrderReady' => [App\Mail\Orders\OrderReadyMail::class],
    'ReviewRequest' => [App\Mail\Customers\ReviewRequestMail::class],
]);

test('StaffInvitationMail renders', function () {
    $invitation = App\Models\Staff\StaffInvitation::factory()->create();

    expect((new App\Mail\Platform\StaffInvitationMail($invitation, 'Sweet Bakery', 'https://example.com/accept'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('CustomerBlastMail renders', function () {
    expect((new App\Mail\Marketing\CustomerBlastMail('Sale this weekend!', '<p>50% off all cakes</p>'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('NewSubscriberNotificationMail renders', function () {
    expect((new App\Mail\Platform\NewSubscriberNotificationMail('Jane', 'jane@example.com', 'Sweet Bakery', 'sweet-bakery', 'starter'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('BirthdayDiscountMail renders', function () {
    $customer = Customer::factory()->create();
    $coupon = App\Models\Financial\Coupon::factory()->percentage()->create();

    expect((new App\Mail\Customers\BirthdayDiscountMail($customer, $coupon))->render())
        ->toBeString()->not->toBeEmpty();
});

test('HappyBirthdayMail renders', function () {
    $customer = Customer::factory()->create();

    expect((new App\Mail\Customers\HappyBirthdayMail($customer))->render())
        ->toBeString()->not->toBeEmpty();
});

test('ProductAvailableMail renders', function () {
    $product = App\Models\Inventory\Product::factory()->create();

    expect((new App\Mail\Customers\ProductAvailableMail($product, 'Jane'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('PurchaseOrderMail renders', function () {
    $items = [['name' => 'Flour', 'sku' => 'FL-001', 'needed' => 10, 'unit' => 'kg', 'unit_price' => 5.00, 'subtotal' => 50.00]];

    expect((new App\Mail\Orders\PurchaseOrderMail('Acme Supplies', 'Sweet Bakery', $items, 100.00, '2026-04-15'))->render())
        ->toBeString()->not->toBeEmpty();
});

test('OrderBakingMail renders', function () {
    $order = Order::factory()->create(['delivery_date' => now()->addDays(3), 'delivery_time' => now()]);
    $order->load('customer', 'orderItems.product');

    expect((new App\Mail\Orders\OrderBakingMail($order))->render())->toBeString()->not->toBeEmpty();
});

test('OrderCancelledMail renders', function () {
    $order = Order::factory()->create(['delivery_date' => now()->addDays(3)]);
    $order->load('customer', 'orderItems.product');

    expect((new App\Mail\Orders\OrderCancelledMail($order))->render())->toBeString()->not->toBeEmpty();
});

test('CateringQuoteMail renders', function () {
    $inquiry = App\Models\Customers\CateringInquiry::factory()->create(['quoted_amount' => 500.00]);

    expect((new App\Mail\Marketing\CateringQuoteMail($inquiry))->render())->toBeString()->not->toBeEmpty();
});

test('NewOrderMessageMail renders', function () {
    $order = Order::factory()->create();
    $message = $order->messages()->create([
        'sender_type' => App\Enums\Orders\SenderType::Customer,
        'sender_name' => 'Jane',
        'message' => 'Can I add extra frosting?',
    ]);

    expect((new App\Mail\Orders\NewOrderMessageMail($message))->render())->toBeString()->not->toBeEmpty();
});

test('HealthAlertMail renders', function () {
    expect((new App\Mail\Platform\HealthAlertMail('Database connection failed'))->render())->toBeString()->not->toBeEmpty();
});

test('ScheduledCheckinMail renders', function () {
    expect((new App\Mail\Platform\ScheduledCheckinMail('Weekly check-in report', 'Weekly Checkin'))->render())->toBeString()->not->toBeEmpty();
});

test('TrialReminderMail renders', function () {
    $user = App\Models\Staff\User::factory()->owner()->create();

    expect((new App\Mail\Platform\TrialReminderMail($user, 'Sweet Bakery', 3))->render())->toBeString()->not->toBeEmpty();
});

test('WeeklyDigestMail renders', function () {
    $mail = new App\Mail\Platform\WeeklyDigestMail(
        stats: ['total_orders' => 10, 'total_revenue' => '$500.00', 'new_customers' => 3, 'avg_order_value' => '$50.00'],
        topProducts: new Illuminate\Database\Eloquent\Collection,
        atRiskCustomers: new Illuminate\Database\Eloquent\Collection,
        upcomingCount: 5,
        storeName: 'Test Bakery',
        adminUrl: 'https://test.kneadit.test/admin',
    );

    expect($mail->render())->toBeString()->not->toBeEmpty();
});

test('PaymentFailedMail renders', function () {
    $user = App\Models\Staff\User::factory()->owner()->create();

    expect((new App\Mail\Platform\PaymentFailedMail($user))->render())->toBeString()->not->toBeEmpty();
});

test('TrialExpiredMail renders', function () {
    $user = App\Models\Staff\User::factory()->owner()->create();

    expect((new App\Mail\Platform\TrialExpiredMail($user, 'test-tenant'))->render())->toBeString()->not->toBeEmpty();
});
