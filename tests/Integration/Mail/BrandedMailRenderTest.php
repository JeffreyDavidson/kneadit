<?php

use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Marketing\CustomerBlastMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Test Bakery',
        'store_email' => 'test@bakery.com',
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
    ]);
});

test('CustomerBlast has dynamic subject', function () {
    $mail = new CustomerBlastMail('Spring Sale!', '<p>20% off</p>');

    expect($mail->envelope()->subject)->toBe('Spring Sale!');
});

test('RepeatOrderReminder has correct subject', function () {
    $customer = Customer::factory()->create();
    $mail = new RepeatOrderReminderMail($customer, 30);

    expect($mail->envelope()->subject)->toContain('We Miss You');
});

test('ProductAvailable has correct subject with product name', function () {
    $product = App\Models\Inventory\Product::factory()->create(['name' => 'Sourdough Loaf']);
    $mail = new App\Mail\Customers\ProductAvailableMail($product, 'Alice');

    expect($mail->envelope()->subject)->toContain('Sourdough Loaf')
        ->and($mail->envelope()->subject)->toContain('Test Bakery');
});

test('PurchaseOrder has correct subject', function () {
    $mail = new App\Mail\Orders\PurchaseOrderMail('Flour Mill', 'Test Bakery', [
        ['name' => 'Bread Flour', 'quantity' => 50, 'unit' => 'lbs'],
    ], 125.00, '2026-04-01');

    expect($mail->envelope()->subject)->toBe('Purchase Order from Test Bakery');
});

test('StaffInvitationMail has correct subject with store name', function () {
    $invitation = App\Models\Staff\StaffInvitation::factory()->create();
    $mail = new App\Mail\Platform\StaffInvitationMail($invitation, 'Test Bakery', 'https://example.test/accept');

    expect($mail->envelope()->subject)->toContain('Test Bakery')
        ->and($mail->envelope()->subject)->toContain('invited');
});

test('HappyBirthday has correct subject with customer name', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);
    $mail = new App\Mail\Customers\HappyBirthdayMail($customer);

    expect($mail->envelope()->subject)->toContain('Alice')
        ->and($mail->envelope()->subject)->toContain('Birthday');
});

test('BirthdayDiscount has correct subject', function () {
    $customer = Customer::factory()->create();
    $coupon = App\Models\Financial\Coupon::factory()->create();
    $mail = new App\Mail\Customers\BirthdayDiscountMail($customer, $coupon);

    expect($mail->envelope()->subject)->toContain('Birthday');
});

test('ReviewRequest has correct subject with store name', function () {
    $order = Order::factory()->create();
    $mail = new App\Mail\Customers\ReviewRequestMail($order);

    expect($mail->envelope()->subject)->toContain('Test Bakery');
});

test('NewOrderMessage has correct subject with order number', function () {
    $order = Order::factory()->create();
    $message = App\Models\Orders\OrderMessage::factory()->recycle($order)->fromBaker()->create([
        'message' => 'Your order is ready!',
        'sender_name' => 'Baker',
    ]);
    $mail = new App\Mail\Orders\NewOrderMessageMail($message);

    expect($mail->envelope()->subject)->toContain($order->order_number);
});

test('CateringQuote has correct subject with store name', function () {
    $inquiry = App\Models\Customers\CateringInquiry::factory()->create();
    $mail = new App\Mail\Marketing\CateringQuoteMail($inquiry);

    expect($mail->envelope()->subject)->toContain('Catering Quote')
        ->and($mail->envelope()->subject)->toContain('Test Bakery');
});

test('WeeklyDigest has correct subject with store name', function () {
    $mail = new App\Mail\Platform\WeeklyDigestMail(
        stats: [],
        topProducts: App\Models\Orders\OrderItem::query()->whereKey([])->get(),
        atRiskCustomers: Customer::query()->whereKey([])->get(),
        upcomingCount: 0,
        storeName: 'Test Bakery',
        adminUrl: 'https://test.kneadit.test/admin',
    );

    expect($mail->envelope()->subject)->toContain('Weekly Digest')
        ->and($mail->envelope()->subject)->toContain('Test Bakery');
});
