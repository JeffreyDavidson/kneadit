<?php

use App\Enums\Financial\CouponType;
use App\Enums\Marketing\EmailTemplateType;
use App\Enums\Orders\OrderStatus;
use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Mail\Customers\CustomerReferralRewardMail;
use App\Mail\Customers\HappyBirthdayMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Orders\OrderModifiedMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerReferral;
use App\Models\Financial\Coupon;
use App\Models\Inventory\Product;
use App\Models\Marketing\EmailTemplate;
use App\Models\Orders\Cart;
use App\Models\Orders\Order;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Test Bakery',
        'store_email' => 'test@bakery.com',
    ]);
});

test('OrderPlacedMail resolves default, custom, and subject-only templates', function () {
    $order = Order::factory()->create();

    $defaultMail = new OrderPlacedMail($order);

    expect($defaultMail->envelope()->subject)->toContain('Received — Test Bakery')
        ->and($defaultMail->content()->view)->toBe('emails.orders.order-placed');

    $template = EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Thanks {customer_name}! Order #{order_number} received',
        'body' => '<p>Custom body</p>',
    ]);

    $customMail = new OrderPlacedMail($order);

    expect($customMail->envelope()->subject)->toContain('Order #' . $order->order_number . ' received')
        ->and($customMail->content()->view)->toBe('emails.custom-template');

    $template->update([
        'subject' => 'Subject only for #{order_number}',
        'body' => '',
    ]);

    $subjectOnlyMail = new OrderPlacedMail($order);

    expect($subjectOnlyMail->envelope()->subject)->toContain("Subject only for #{$order->order_number}")
        ->and($subjectOnlyMail->content()->view)->toBe('emails.orders.order-placed');
});

test('OrderStatusMail resolves status-specific custom and default templates', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderConfirmed,
        'subject' => 'Confirmed! Order #{order_number}',
        'body' => '<p>Your order is confirmed</p>',
    ]);

    $confirmedMail = new OrderStatusMail($order, OrderStatus::Confirmed);

    expect($confirmedMail->envelope()->subject)->toBe("Confirmed! Order #{$order->order_number}")
        ->and($confirmedMail->content()->view)->toBe('emails.custom-template');

    $bakingMail = new OrderStatusMail($order, OrderStatus::Baking);

    expect($bakingMail->envelope()->subject)->toContain('is Being Prepared')
        ->and($bakingMail->content()->html)->toBe('emails.orders.order-baking');
});

test('customer mailables resolve their custom templates', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::ReviewRequest,
        'subject' => 'Tell us about your order from {store_name}',
        'body' => '<p>We would love your feedback</p>',
    ]);

    $reviewRequestMail = new ReviewRequestMail($order);

    expect($reviewRequestMail->envelope()->subject)->toBe('Tell us about your order from Test Bakery')
        ->and($reviewRequestMail->content()->view)->toBe('emails.custom-template');

    $customer = Customer::factory()->create(['name' => 'Jane Doe']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::HappyBirthday,
        'subject' => 'Happy Birthday {customer_name} from {store_name}!',
        'body' => '<p>Enjoy your special day</p>',
    ]);

    $happyBirthdayMail = new HappyBirthdayMail($customer);

    expect($happyBirthdayMail->envelope()->subject)->toBe('Happy Birthday Jane Doe from Test Bakery!');

    $repeatCustomer = Customer::factory()->create(['name' => 'Bob']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::RepeatOrderReminder,
        'subject' => 'Hey {customer_name}, it has been {days_since_last_order} days!',
        'body' => '<p>Come back!</p>',
    ]);

    $repeatOrderReminderMail = new RepeatOrderReminderMail($repeatCustomer, 14);

    expect($repeatOrderReminderMail->envelope()->subject)->toBe('Hey Bob, it has been 14 days!');

    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::ProductAvailable,
        'subject' => '{product_name} is back!',
        'body' => '<p>Get it while it lasts</p>',
    ]);

    $productAvailableMail = new ProductAvailableMail($product, 'Alice');

    expect($productAvailableMail->envelope()->subject)->toBe('Sourdough Loaf is back!');

    $referrer = Customer::factory()->create(['name' => 'Sam']);
    $referral = CustomerReferral::factory()->create(['referrer_customer_id' => $referrer->id]);
    $coupon = Coupon::factory()->create([
        'code' => 'THANKS10',
        'type' => CouponType::Fixed,
        'fixed_amount' => 1000,
    ]);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::CustomerReferralReward,
        'subject' => '{customer_name}, your reward {coupon_code} is ready',
        'body' => '<p>Use {coupon_code}</p>',
    ]);

    $referralRewardMail = new CustomerReferralRewardMail($referral, $coupon);

    expect($referralRewardMail->envelope()->subject)->toBe('Sam, your reward THANKS10 is ready')
        ->and($referralRewardMail->content()->view)->toBe('emails.custom-template');
});

test('AbandonedCartRecoveryMail resolves default and custom templates', function () {
    $cart = Cart::factory()->create(['customer_name' => 'Maya']);

    $defaultMail = new AbandonedCartRecoveryMail($cart);

    expect($defaultMail->envelope()->subject)->toBe('You left something in your cart')
        ->and($defaultMail->content()->view)->toBe('emails.customers.abandoned-cart-recovery');

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::AbandonedCartRecovery,
        'subject' => 'Hey {customer_name}, come back to {store_name}',
        'body' => '<p>Your cart is waiting</p>',
    ]);

    $customMail = new AbandonedCartRecoveryMail($cart);

    expect($customMail->envelope()->subject)->toBe('Hey Maya, come back to Test Bakery')
        ->and($customMail->content()->view)->toBe('emails.custom-template');
});

test('OrderModifiedMail resolves default and custom templates', function () {
    $order = Order::factory()->create();

    $defaultMail = new OrderModifiedMail($order, Money::fromCents(1000), Money::fromCents(2000));

    expect($defaultMail->envelope()->subject)->toBe("Your order #{$order->order_number} was updated")
        ->and($defaultMail->content()->view)->toBe('emails.orders.order-modified');

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderModified,
        'subject' => 'Heads up — order #{order_number} changed',
        'body' => '<p>Was {previous_total}, now {new_total}</p>',
    ]);

    $customMail = new OrderModifiedMail($order, Money::fromCents(1000), Money::fromCents(2000));

    expect($customMail->envelope()->subject)->toBe("Heads up — order #{$order->order_number} changed")
        ->and($customMail->content()->view)->toBe('emails.custom-template');
});

test('custom template body strips dangerous HTML when rendered', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Order received',
        'body' => '<p>Thanks!</p><script>alert("xss")</script><img src=x onerror="alert(1)">',
    ]);

    $rendered = (new OrderPlacedMail($order))->render();

    expect($rendered)
        ->not->toContain('<script>')
        ->not->toContain('alert("xss")')
        ->not->toContain('onerror=')
        ->toContain('Thanks!');
});
