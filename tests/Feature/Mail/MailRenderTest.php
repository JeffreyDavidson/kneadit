<?php

use App\Enums\Orders\OrderStatus;
use App\Mail\Customers\NewContactMessageNotificationMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Orders\OrderStatusMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Customers\ContactMessage;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;

beforeEach(fn () => setUpTenantTest());

test('order mail classes render without errors', function () {
    $order = Order::factory()
        ->withDeliveryDate(now()->addDays(3))
        ->create(['delivery_time' => now()]);
    $order->load('customer', 'orderItems.product');

    expect((new OrderStatusMail($order, OrderStatus::Delivered))->render())
        ->toBeString()
        ->not->toBeEmpty();

    foreach ([
        App\Mail\Orders\NewOrderNotificationMail::class,
        App\Mail\Orders\OrderPlacedMail::class,
        App\Mail\Customers\ReviewRequestMail::class,
    ] as $mailClass) {
        expect((new $mailClass($order))->render())
            ->toBeString()
            ->not->toBeEmpty();
    }

    foreach ([
        OrderStatus::Confirmed,
        OrderStatus::Baking,
        OrderStatus::Ready,
        OrderStatus::Delivered,
        OrderStatus::Cancelled,
    ] as $status) {
        expect((new OrderStatusMail($order, $status))->render())
            ->toBeString()
            ->not->toBeEmpty();
    }

    $message = $order->messages()->create([
        'sender_type' => App\Enums\Orders\SenderType::Customer,
        'sender_name' => 'Jane',
        'message' => 'Can I add extra frosting?',
    ]);

    expect((new App\Mail\Orders\NewOrderMessageMail($message))->render())
        ->toBeString()
        ->not->toBeEmpty();
});

test('customer and marketing mail classes render without errors', function () {
    $customer = Customer::factory()->create();

    expect((new RepeatOrderReminderMail($customer, 30))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $message = ContactMessage::factory()->create([
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'subject' => 'Custom cake',
        'message' => 'Can you make a birthday cake?',
    ]);

    expect((new NewContactMessageNotificationMail($message))->render())
        ->toContain('Jane Baker')
        ->toContain('Can you make a birthday cake?');

    expect((new App\Mail\Customers\HappyBirthdayMail($customer))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $product = App\Models\Inventory\Product::factory()->create();

    expect((new App\Mail\Customers\ProductAvailableMail($product, 'Jane'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $inquiry = App\Models\Customers\CateringInquiry::factory()->create([
        'quoted_amount' => 500.00,
    ]);

    expect((new App\Mail\Marketing\CateringQuoteMail($inquiry))->render())
        ->toBeString()
        ->not->toBeEmpty();
});

test('platform account mail classes render without errors', function () {
    expect((new WelcomeBakerMail('Jane', 'Sweet Treats', 'https://example.com/admin', 'starter', 'https://example.com/getting-started'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $invitation = App\Models\Staff\StaffInvitation::factory()->create();

    expect((new App\Mail\Platform\StaffInvitationMail($invitation, 'Sweet Bakery', 'https://example.com/accept'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    expect((new App\Mail\Platform\NewSubscriberNotificationMail('Jane', 'jane@example.com', 'Sweet Bakery', 'sweet-bakery', 'starter'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $user = App\Models\Staff\User::factory()->owner()->create();

    expect((new App\Mail\Platform\TrialReminderMail($user, 'Sweet Bakery', 3))->render())
        ->toBeString()
        ->not->toBeEmpty();

    expect((new App\Mail\Platform\PaymentFailedMail($user))->render())
        ->toBeString()
        ->not->toBeEmpty();

    expect((new App\Mail\Platform\TrialExpiredMail($user, 'https://test-tenant.kneadit.test/admin'))->render())
        ->toBeString()
        ->not->toBeEmpty();
});

test('standalone operational mail classes render without errors', function () {
    expect((new App\Mail\Marketing\CustomerBlastMail('Sale this weekend!', '<p>50% off all cakes</p>'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $items = [
        [
            'name' => 'Flour',
            'sku' => 'FL-001',
            'needed' => 10,
            'unit' => 'kg',
            'unit_price' => 5.00,
            'subtotal' => 50.00,
        ],
    ];

    expect((new App\Mail\Orders\PurchaseOrderMail('Acme Supplies', 'Sweet Bakery', $items, 100.00, '2026-04-15'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    expect((new App\Mail\Platform\HealthAlertMail('Database connection failed'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    expect((new App\Mail\Platform\ScheduledCheckinMail('Weekly check-in report', 'Weekly Checkin'))->render())
        ->toBeString()
        ->not->toBeEmpty();

    $weeklyDigest = new App\Mail\Platform\WeeklyDigestMail(
        stats: [
            'total_orders' => 10,
            'total_revenue' => '$500.00',
            'new_customers' => 3,
            'avg_order_value' => '$50.00',
        ],
        topProducts: new Illuminate\Database\Eloquent\Collection,
        atRiskCustomers: new Illuminate\Database\Eloquent\Collection,
        upcomingCount: 5,
        storeName: 'Test Bakery',
        adminUrl: 'https://test.kneadit.test/admin',
    );

    expect($weeklyDigest->render())
        ->toBeString()
        ->not->toBeEmpty();
});
