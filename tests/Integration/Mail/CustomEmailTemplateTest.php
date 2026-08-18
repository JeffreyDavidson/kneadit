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

test('OrderPlacedMail uses custom subject when template exists', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Thanks {customer_name}! Order #{order_number} received',
        'body' => '<p>Custom body</p>',
    ]);

    $mail = new OrderPlacedMail($order);

    expect($mail->envelope()->subject)->toContain('Order #' . $order->order_number . ' received');
});

test('OrderPlacedMail uses default subject when no template exists', function () {
    $order = Order::factory()->create();

    $mail = new OrderPlacedMail($order);

    expect($mail->envelope()->subject)->toContain('Received — Test Bakery');
});

test('OrderPlacedMail uses custom view when template exists', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Custom subject',
        'body' => '<p>Custom body</p>',
    ]);

    $mail = new OrderPlacedMail($order);
    $content = $mail->content();

    expect($content->view)->toBe('emails.custom-template');
});

test('OrderPlacedMail uses default view when no template exists', function () {
    $order = Order::factory()->create();

    $mail = new OrderPlacedMail($order);
    $content = $mail->content();

    expect($content->view)->toBe('emails.orders.order-placed');
});

test('OrderPlacedMail uses custom subject but default view when body is empty', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderPlaced,
        'subject' => 'Subject only for #{order_number}',
        'body' => '',
    ]);

    $mail = new OrderPlacedMail($order);

    expect($mail->envelope()->subject)->toContain("Subject only for #{$order->order_number}")
        ->and($mail->content()->view)->toBe('emails.orders.order-placed');
});

test('OrderStatusMail uses custom template for specific status', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderConfirmed,
        'subject' => 'Confirmed! Order #{order_number}',
        'body' => '<p>Your order is confirmed</p>',
    ]);

    $mail = new OrderStatusMail($order, OrderStatus::Confirmed);

    expect($mail->envelope()->subject)->toBe("Confirmed! Order #{$order->order_number}")
        ->and($mail->content()->view)->toBe('emails.custom-template');
});

test('OrderStatusMail falls back to default for status without custom template', function () {
    $order = Order::factory()->create();

    $mail = new OrderStatusMail($order, OrderStatus::Baking);

    expect($mail->envelope()->subject)->toContain('is Being Prepared')
        ->and($mail->content()->html)->toBe('emails.orders.order-baking');
});

test('ReviewRequestMail uses custom template when exists', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::ReviewRequest,
        'subject' => 'Tell us about your order from {store_name}',
        'body' => '<p>We would love your feedback</p>',
    ]);

    $mail = new ReviewRequestMail($order);

    expect($mail->envelope()->subject)->toBe('Tell us about your order from Test Bakery')
        ->and($mail->content()->view)->toBe('emails.custom-template');
});

test('HappyBirthdayMail uses custom template when exists', function () {
    $customer = Customer::factory()->create(['name' => 'Jane Doe']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::HappyBirthday,
        'subject' => 'Happy Birthday {customer_name} from {store_name}!',
        'body' => '<p>Enjoy your special day</p>',
    ]);

    $mail = new HappyBirthdayMail($customer);

    expect($mail->envelope()->subject)->toBe('Happy Birthday Jane Doe from Test Bakery!');
});

test('RepeatOrderReminderMail uses custom template when exists', function () {
    $customer = Customer::factory()->create(['name' => 'Bob']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::RepeatOrderReminder,
        'subject' => 'Hey {customer_name}, it has been {days_since_last_order} days!',
        'body' => '<p>Come back!</p>',
    ]);

    $mail = new RepeatOrderReminderMail($customer, 14);

    expect($mail->envelope()->subject)->toBe('Hey Bob, it has been 14 days!');
});

test('ProductAvailableMail uses custom template when exists', function () {
    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::ProductAvailable,
        'subject' => '{product_name} is back!',
        'body' => '<p>Get it while it lasts</p>',
    ]);

    $mail = new ProductAvailableMail($product, 'Alice');

    expect($mail->envelope()->subject)->toBe('Sourdough Loaf is back!');
});

test('AbandonedCartRecoveryMail uses custom template when exists', function () {
    $cart = Cart::factory()->create(['customer_name' => 'Maya']);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::AbandonedCartRecovery,
        'subject' => 'Hey {customer_name}, come back to {store_name}',
        'body' => '<p>Your cart is waiting</p>',
    ]);

    $mail = new AbandonedCartRecoveryMail($cart);

    expect($mail->envelope()->subject)->toBe('Hey Maya, come back to Test Bakery')
        ->and($mail->content()->view)->toBe('emails.custom-template');
});

test('AbandonedCartRecoveryMail falls back to default when no template exists', function () {
    $cart = Cart::factory()->create();

    $mail = new AbandonedCartRecoveryMail($cart);

    expect($mail->envelope()->subject)->toBe('You left something in your cart')
        ->and($mail->content()->view)->toBe('emails.customers.abandoned-cart-recovery');
});

test('CustomerReferralRewardMail uses custom template when exists', function () {
    $referrer = Customer::factory()->create(['name' => 'Sam']);
    $referral = CustomerReferral::factory()->create(['referrer_customer_id' => $referrer->id]);
    $coupon = Coupon::factory()->create(['code' => 'THANKS10', 'type' => CouponType::Fixed, 'fixed_amount' => 1000]);

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::CustomerReferralReward,
        'subject' => '{customer_name}, your reward {coupon_code} is ready',
        'body' => '<p>Use {coupon_code}</p>',
    ]);

    $mail = new CustomerReferralRewardMail($referral, $coupon);

    expect($mail->envelope()->subject)->toBe('Sam, your reward THANKS10 is ready')
        ->and($mail->content()->view)->toBe('emails.custom-template');
});

test('OrderModifiedMail uses custom template when exists', function () {
    $order = Order::factory()->create();

    EmailTemplate::factory()->create([
        'email_type' => EmailTemplateType::OrderModified,
        'subject' => 'Heads up — order #{order_number} changed',
        'body' => '<p>Was {previous_total}, now {new_total}</p>',
    ]);

    $mail = new OrderModifiedMail($order, Money::fromCents(1000), Money::fromCents(2000));

    expect($mail->envelope()->subject)->toBe("Heads up — order #{$order->order_number} changed")
        ->and($mail->content()->view)->toBe('emails.custom-template');
});

test('OrderModifiedMail falls back to default when no template exists', function () {
    $order = Order::factory()->create();

    $mail = new OrderModifiedMail($order, Money::fromCents(1000), Money::fromCents(2000));

    expect($mail->envelope()->subject)->toBe("Your order #{$order->order_number} was updated")
        ->and($mail->content()->view)->toBe('emails.orders.order-modified');
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
