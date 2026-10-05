<?php

use App\Enums\Orders\OrderStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use App\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    test()->driverMiddleware = [
        InitializeTenancyByDomainOrSubdomain::class,
        PreventAccessFromCentralDomains::class,
    ];
});

function createDeliveryOrder(array $attrs = []): Order
{
    $user = User::factory()->owner()->create(['email' => 'baker@test.com']);
    $customer = Customer::factory()->create();

    return Order::factory()
        ->for($customer)
        ->recycle($user)
        ->ready()
        ->create(array_merge([
            'delivery_address' => '123 Main St',
            'delivery_date' => today(),
        ], $attrs));
}

test('driver page requires a signed-in staff member', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder([
        'order_number' => 'ORD-PRIVATE',
        'delivery_address' => '42 Private Lane',
    ]);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->get(route('driver.index', [], false));

    $response->assertRedirect(route('filament.admin.auth.login'));
    $response->assertDontSee('ORD-PRIVATE');
    $response->assertDontSee('42 Private Lane');
});

test('driver page loads', function () {
    settings(['store_name' => 'Test Bakery']);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $response->assertOk();
});

test('driver page shows todays delivery orders', function () {
    settings(['store_name' => 'Test Bakery']);
    $order = createDeliveryOrder(['order_number' => 'ORD-001']);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $response->assertOk();
    $response->assertSee('ORD-001');
});

test('driver page hides pickup orders', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-PICKUP', 'delivery_address' => '']);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $response->assertOk();
    $response->assertDontSee('ORD-PICKUP');
});

test('driver page hides past orders', function () {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-OLD', 'delivery_date' => today()->subDay()]);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $response->assertOk();
    $response->assertDontSee('ORD-OLD');
});

test('mark delivered changes order status', function () {
    $order = createDeliveryOrder(['status' => OrderStatus::Ready]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware(test()->driverMiddleware)
        ->post(route('driver.delivered', $order->order_number, false));

    $response->assertRedirect();
    expect($order->fresh()->status)->toBe(OrderStatus::Delivered);
});

test('mark delivered redirects back', function () {
    $order = createDeliveryOrder(['status' => OrderStatus::Ready]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware(test()->driverMiddleware)
        ->from(route('driver.index', [], false))
        ->post(route('driver.delivered', $order->order_number, false));

    $response->assertRedirect(route('driver.index', [], false));
});

test('driver page lists only orders that are ready for delivery', function (OrderStatus $status, bool $listed) {
    settings(['store_name' => 'Test Bakery']);
    createDeliveryOrder(['order_number' => 'ORD-STATUS', 'status' => $status]);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $listed ? $response->assertSee('ORD-STATUS') : $response->assertDontSee('ORD-STATUS');
})->with([
    'confirmed' => [OrderStatus::Confirmed, false],
    'baking' => [OrderStatus::Baking, false],
    'ready' => [OrderStatus::Ready, true],
    'delivered' => [OrderStatus::Delivered, false],
    'cancelled' => [OrderStatus::Cancelled, false],
]);

test('driver page follows the bakery timezone for today', function () {
    Date::setTestNow('2026-10-05 02:00');
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/Los_Angeles'])));
    createDeliveryOrder(['order_number' => 'ORD-LOCAL', 'delivery_date' => '2026-10-04']);
    Order::factory()->ready()->create(['order_number' => 'ORD-UTC', 'delivery_date' => '2026-10-05', 'delivery_address' => '9 Other St']);

    $response = withoutMiddleware(test()->driverMiddleware)
        ->actingAs(User::factory()->staff()->create())
        ->get(route('driver.index', [], false));

    $response->assertSee('ORD-LOCAL')
        ->assertDontSee('ORD-UTC')
        ->assertSee('Sunday, Oct 4');
});

test('marking an order that is not ready as delivered redirects back with an error', function (OrderStatus $status) {
    $order = createDeliveryOrder(['status' => $status]);
    $user = User::query()->firstWhere('email', 'baker@test.com');

    $response = actingAs($user)
        ->withoutMiddleware(test()->driverMiddleware)
        ->from(route('driver.index', [], false))
        ->post(route('driver.delivered', $order->order_number, false));

    $response->assertRedirect(route('driver.index', [], false))
        ->assertSessionHas('error')
        ->assertSessionMissing('success');
    expect($order->fresh()->status)->toBe($status);
})->with([
    'confirmed' => OrderStatus::Confirmed,
    'baking' => OrderStatus::Baking,
    'already delivered' => OrderStatus::Delivered,
]);
