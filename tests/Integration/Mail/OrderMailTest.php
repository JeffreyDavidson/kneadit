<?php

use App\Enums\Marketing\EmailTemplateType;
use App\Enums\Orders\OrderStatus;
use App\Mail\Orders\NewOrderNotificationMail;
use App\Mail\Orders\OrderPlacedMail;
use App\Mail\Orders\OrderStatusMail;
use App\Models\Marketing\EmailTemplate;
use App\Models\Orders\Order;
use App\Services\Settings\TenantSettings;
use App\ValueObjects\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    settings([
        'store_name' => 'Test Bakery',
        'brand_color_primary' => '#d4920c',
        'brand_color_secondary' => '#1c1410',
    ]);
    test()->order = Order::factory()->create();
});

test('order mailables have correct envelope subjects', function (string $mailClass, array $args, string $expectedSubjectFragment) {
    $mail = new $mailClass(...$args);
    $subject = $mail->envelope()->subject;

    expect($subject)->toContain($expectedSubjectFragment);
})->with([
    'OrderPlacedMail' => [OrderPlacedMail::class, fn () => [test()->order], 'Received — Test Bakery'],
    'OrderStatusMail (Confirmed)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Confirmed], 'Confirmed — Test Bakery'],
    'OrderStatusMail (Ready)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Ready], 'is Ready!'],
    'OrderStatusMail (Baking)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Baking], 'is Being Prepared'],
    'OrderStatusMail (Delivered)' => [OrderStatusMail::class, fn () => [Order::factory()->delivery()->create(), OrderStatus::Delivered], 'Delivered'],
    'OrderStatusMail (Cancelled)' => [OrderStatusMail::class, fn () => [test()->order, OrderStatus::Cancelled], 'Cancelled'],
    'NewOrderNotificationMail' => [NewOrderNotificationMail::class, fn () => [test()->order], 'New Order #'],
]);

test('order mail custom templates render the order total once with a single dollar sign', function (EmailTemplateType $type, callable $mail) {
    $order = Order::factory()->create(['total' => Money::fromDollars(12)]);
    EmailTemplate::factory()->create([
        'email_type' => $type,
        'subject' => 'Total {order_total}',
        'body' => '<p>Total {order_total}</p>',
    ]);

    $rendered = $mail($order);

    expect($rendered->envelope()->subject)->toBe('Total $12.00')
        ->and($rendered->content()->with['customBody'])->toBe('<p>Total $12.00</p>');
})->with([
    'placed' => [EmailTemplateType::OrderPlaced, fn (Order $order) => new OrderPlacedMail($order)],
    'status' => [EmailTemplateType::OrderConfirmed, fn (Order $order) => new OrderStatusMail($order, OrderStatus::Confirmed)],
]);

test('the new order notification subject shows the total once with a single dollar sign', function () {
    $order = Order::factory()->create(['total' => Money::fromDollars(12)]);

    $subject = new NewOrderNotificationMail($order)->envelope()->subject;

    expect($subject)->toBe("New Order #{$order->order_number} — \$12.00");
});

test('the default delivered subject says picked up for pickup orders and delivered for delivery orders', function (string $state, string $expected, string $unexpected) {
    $order = Order::factory()->{$state}()->create();

    $subject = new OrderStatusMail($order, OrderStatus::Delivered)->envelope()->subject;

    expect($subject)
        ->toContain($expected)
        ->not->toContain($unexpected);
})->with([
    'pickup' => ['pickup', 'Picked Up', 'Delivered'],
    'delivery' => ['delivery', 'Delivered', 'Picked Up'],
]);

test('the delivered email for a delivery order names the address and the bakery-local time', function () {
    // 2026-10-07 03:30 UTC is 8:30 PM on Oct 6 in Los Angeles.
    Date::setTestNow('2026-10-07 03:30:00');
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/Los_Angeles'])));
    $order = Order::factory()->delivery()->create(['delivery_address' => '12 Rye Lane, Portland']);

    $html = new OrderStatusMail($order, OrderStatus::Delivered)->render();

    expect($html)
        ->toContain('Delivered to:')
        ->toContain('12 Rye Lane, Portland')
        ->toContain('Oct 6, 2026 at 8:30 PM')
        ->not->toContain('Picked up');
});

test('the delivered email for a pickup order says picked up and shows no address', function () {
    Date::setTestNow('2026-10-07 03:30:00');
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/Los_Angeles'])));
    $order = Order::factory()->pickup()->create(['delivery_address' => null]);

    $html = new OrderStatusMail($order, OrderStatus::Delivered)->render();

    expect($html)
        ->toContain('Picked up')
        ->toContain('Oct 6, 2026 at 8:30 PM')
        ->not->toContain('Delivered to:')
        ->not->toContain('Delivery Confirmed')
        ->not->toContain('Delivery Complete')
        ->not->toContain('safely delivered');
});
