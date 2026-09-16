<?php

use App\Mail\Customers\HappyBirthdayMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Marketing\CateringQuoteMail;
use App\Mail\Marketing\CustomerBlastMail;
use App\Mail\Orders\NewOrderMessageMail;
use App\Mail\Orders\PurchaseOrderMail;
use App\Mail\Platform\StaffInvitationMail;
use App\Mail\Platform\WeeklyDigestMail;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderMessage;
use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

pest()->use(RefreshDatabase::class);

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
    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);
    $mail = new ProductAvailableMail($product, 'Alice');

    expect($mail->envelope()->subject)->toContain('Sourdough Loaf')
        ->and($mail->envelope()->subject)->toContain('Test Bakery');
});

test('PurchaseOrder has correct subject', function () {
    $mail = new PurchaseOrderMail('Flour Mill', 'Test Bakery', [
        ['name' => 'Bread Flour', 'quantity' => 50, 'unit' => 'lbs'],
    ], 125.00, '2026-04-01');

    expect($mail->envelope()->subject)->toBe('Purchase Order from Test Bakery');
});

test('StaffInvitationMail has correct subject with store name', function () {
    URL::forceRootUrl('https://platform.kneadit.test');
    URL::forceScheme('https');

    $invitation = StaffInvitation::factory()->create();
    $mail = new StaffInvitationMail($invitation, 'Test Bakery', 'https://example.test/accept');

    expect($mail->envelope()->subject)->toContain('Test Bakery')
        ->and($mail->envelope()->subject)->toContain('invited')
        ->and($mail->render())->toContain('href="https://platform.kneadit.test"');
});

test('HappyBirthday has correct subject with customer name', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);
    $mail = new HappyBirthdayMail($customer);

    expect($mail->envelope()->subject)->toContain('Alice')
        ->and($mail->envelope()->subject)->toContain('Birthday');
});

test('ReviewRequest has correct subject with store name', function () {
    $order = Order::factory()->create();
    $mail = new ReviewRequestMail($order);

    expect($mail->envelope()->subject)->toContain('Test Bakery');
});

test('NewOrderMessage has correct subject with order number', function () {
    $order = Order::factory()->create();
    $message = OrderMessage::factory()->recycle($order)->fromBaker()->create([
        'message' => 'Your order is ready!',
        'sender_name' => 'Baker',
    ]);
    $mail = new NewOrderMessageMail($message);

    expect($mail->envelope()->subject)->toContain($order->order_number);
});

test('CateringQuote has correct subject with store name', function () {
    $inquiry = CateringInquiry::factory()->create();
    $mail = new CateringQuoteMail($inquiry);

    expect($mail->envelope()->subject)->toContain('Catering Quote')
        ->and($mail->envelope()->subject)->toContain('Test Bakery');
});

test('WeeklyDigest has correct subject with store name', function () {
    $mail = new WeeklyDigestMail(
        stats: [],
        topProducts: OrderItem::query()->whereKey([])->get(),
        atRiskCustomers: Customer::query()->whereKey([])->get(),
        upcomingCount: 0,
        storeName: 'Test Bakery',
        adminUrl: 'https://test.kneadit.test/admin',
    );

    expect($mail->envelope()->subject)->toContain('Weekly Digest')
        ->and($mail->envelope()->subject)->toContain('Test Bakery');
});
