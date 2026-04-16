<?php

use App\Enums\Marketing\EmailTemplateType;
use App\Enums\Orders\OrderStatus;
use App\Mail\Customers\HappyBirthdayMail;
use App\Mail\Customers\ProductAvailableMail;
use App\Mail\Customers\RepeatOrderReminderMail;
use App\Mail\Customers\ReviewRequestMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Marketing\EmailTemplate;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Test Bakery',
        'store_email' => 'test@bakery.com',
    ]);
});

test('OrderPlacedMail uses custom subject when template exists', function () {
    $order = Order::factory()->create();

    EmailTemplate::create([
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

    EmailTemplate::create([
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

    EmailTemplate::create([
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

    EmailTemplate::create([
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

    EmailTemplate::create([
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

    EmailTemplate::create([
        'email_type' => EmailTemplateType::HappyBirthday,
        'subject' => 'Happy Birthday {customer_name} from {store_name}!',
        'body' => '<p>Enjoy your special day</p>',
    ]);

    $mail = new HappyBirthdayMail($customer);

    expect($mail->envelope()->subject)->toBe('Happy Birthday Jane Doe from Test Bakery!');
});

test('RepeatOrderReminderMail uses custom template when exists', function () {
    $customer = Customer::factory()->create(['name' => 'Bob']);

    EmailTemplate::create([
        'email_type' => EmailTemplateType::RepeatOrderReminder,
        'subject' => 'Hey {customer_name}, it has been {days_since_last_order} days!',
        'body' => '<p>Come back!</p>',
    ]);

    $mail = new RepeatOrderReminderMail($customer, 14);

    expect($mail->envelope()->subject)->toBe('Hey Bob, it has been 14 days!');
});

test('ProductAvailableMail uses custom template when exists', function () {
    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);

    EmailTemplate::create([
        'email_type' => EmailTemplateType::ProductAvailable,
        'subject' => '{product_name} is back!',
        'body' => '<p>Get it while it lasts</p>',
    ]);

    $mail = new ProductAvailableMail($product, 'Alice');

    expect($mail->envelope()->subject)->toBe('Sourdough Loaf is back!');
});
