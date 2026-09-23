<?php

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\SenderType;
use App\Mail\Customers\HappyBirthdayMail;
use App\Mail\Customers\NewContactMessageNotificationMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Marketing\CateringQuoteMail;
use App\Mail\Marketing\CustomerBlastMail;
use App\Mail\Orders\NewOrderMessageMail;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Mail\Orders\PurchaseOrderMail;
use App\Mail\Platform\HealthAlertMail;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Mail\Platform\PaymentFailedMail;
use App\Mail\Platform\ScheduledCheckinMail;
use App\Mail\Platform\StaffInvitationMail;
use App\Mail\Platform\TrialExpiredMail;
use App\Mail\Platform\TrialReminderMail;
use App\Mail\Platform\WeeklyDigestMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\ContactMessage;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;
use App\ValueObjects\Money;
use Illuminate\Database\Eloquent\Collection;

beforeEach(fn () => setUpTenantTest());

test('order mail classes render without errors', function () {
    $order = Order::factory()
        ->withDeliveryDate(now()->addDays(3))
        ->create(['delivery_time' => now()]);
    $order->load('customer', 'orderItems.product');

    expect(new OrderStatusMail($order, OrderStatus::Delivered)->render())
        ->toBeString()
        ->not->toBeEmpty();

    foreach ([
        NewOrderNotificationMail::class,
        OrderPlacedMail::class,
        ReviewRequestMail::class,
    ] as $mailClass) {
        expect(new $mailClass($order)->render())
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
        expect(new OrderStatusMail($order, $status)->render())
            ->toBeString()
            ->not->toBeEmpty();
    }

    $message = $order->messages()->create([
        'sender_type' => SenderType::Customer,
        'sender_name' => 'Jane',
        'message' => 'Can I add extra frosting?',
    ]);

    expect(new NewOrderMessageMail($message)->render())
        ->toBeString()
        ->not->toBeEmpty();
});

test('customer and marketing mail classes render without errors', function () {
    $customer = Customer::factory()->create();

    expect(new RepeatOrderReminderMail($customer, 30)->render())
        ->toBeString()
        ->not->toBeEmpty();

    $message = ContactMessage::factory()->create([
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'subject' => 'Custom cake',
        'message' => 'Can you make a birthday cake?',
    ]);

    expect(new NewContactMessageNotificationMail($message)->render())
        ->toContain('Jane Baker')
        ->toContain('Can you make a birthday cake?')
        ->and(new HappyBirthdayMail($customer)->render())->toBeString()->not->toBeEmpty();

    $product = Product::factory()->create();

    expect(new ProductAvailableMail($product, 'Jane')->render())
        ->toBeString()
        ->not->toBeEmpty();

    $inquiry = CateringInquiry::factory()->create([
        'quoted_amount' => 500.00,
    ]);

    expect(new CateringQuoteMail($inquiry)->render())
        ->toBeString()
        ->not->toBeEmpty();
});

test('platform account mail classes render without errors', function () {
    expect(new WelcomeBakerMail('Jane', 'Sweet Treats', 'https://example.com/admin', 'starter', 'https://example.com/getting-started')->render())
        ->toBeString()
        ->not->toBeEmpty();

    $invitation = StaffInvitation::factory()->create();

    expect(new StaffInvitationMail($invitation, 'Sweet Bakery', 'https://example.com/accept')->render())
        ->toBeString()
        ->not->toBeEmpty()
        ->and(new NewSubscriberNotificationMail(
            'Jane',
            'jane@example.com',
            'Sweet Bakery',
            'sweet-bakery.kneadit.test',
            'starter',
            'https://kneadit.test/central',
        )->render())->toBeString()->not->toBeEmpty();

    $user = User::factory()->owner()->create();

    expect(new TrialReminderMail($user, 'Sweet Bakery', 3)->render())
        ->toBeString()
        ->not->toBeEmpty()
        ->and(new PaymentFailedMail($user)->render())->toBeString()->not->toBeEmpty()
        ->and(new TrialExpiredMail($user, 'https://test-tenant.kneadit.test/admin')->render())->toBeString()->not->toBeEmpty();
});

test('standalone operational mail classes render without errors', function () {
    expect(new CustomerBlastMail('Sale this weekend!', '<p>50% off all cakes</p>')->render())
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

    expect(new PurchaseOrderMail('Acme Supplies', 'Sweet Bakery', $items, 100.00, '2026-04-15')->render())
        ->toBeString()
        ->not->toBeEmpty()
        ->and(new HealthAlertMail('Database connection failed')->render())->toBeString()->not->toBeEmpty()
        ->and(new ScheduledCheckinMail('Weekly check-in report', 'Weekly Checkin')->render())->toBeString()->not->toBeEmpty();

    $weeklyDigest = new WeeklyDigestMail(
        stats: [
            'total_orders' => 10,
            'total_revenue' => Money::fromDollars(500),
            'new_customers' => 3,
            'avg_order_value' => Money::fromDollars(50),
        ],
        topProducts: new Collection,
        atRiskCustomers: new Collection,
        upcomingCount: 5,
        storeName: 'Test Bakery',
        adminUrl: 'https://test.kneadit.test/admin',
    );

    expect($weeklyDigest->render())
        ->toBeString()
        ->not->toBeEmpty()
        ->toContain('$500.00', '$50.00')
        ->not->toContain('$$500.00', '$$50.00');
});
