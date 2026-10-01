<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();

    test()->guest = Customer::factory()->create([
        'name' => 'Original Name',
        'email' => 'owner@example.com',
        'phone' => '5550100',
    ]);
    test()->order = Order::factory()
        ->for(test()->guest)
        ->pending()
        ->unpaid()
        ->create(['order_number' => 'ORD-CLAIM-001']);
    test()->item = OrderItem::factory()
        ->for(test()->order)
        ->for(Product::factory())
        ->create(['quantity' => 2, 'unit_price' => 10.00]);
});

function registerForGuestEmail(): TestResponse
{
    return withoutMiddleware(tenantMiddleware())
        ->post(route('account.register', [], false), [
            'name' => 'New Registrant',
            'email' => 'owner@example.com',
            'phone' => '5559999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
}

test('registering with a guest customer email logs in but keeps the existing name and phone', function () {
    registerForGuestEmail();

    $customer = test()->guest->fresh();

    expect(auth('customer')->id())->toBe($customer->id)
        ->and($customer->name)->toBe('Original Name')
        ->and($customer->phone)->toBe('5550100')
        ->and($customer->email_verified_at)->toBeNull();
});

test('an unverified customer is redirected to the verify notice from account pages', function (string $routeName) {
    registerForGuestEmail();

    $response = withoutMiddleware(tenantMiddleware())->get(route($routeName, [], false));

    $response->assertRedirect(route('account.email.verify.notice', [], false));
})->with(['account.dashboard', 'account.orders', 'account.profile.show']);

test('an unverified customer cannot update the profile', function () {
    registerForGuestEmail();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.profile.update', [], false), ['name' => 'Changed']);

    $response->assertRedirect(route('account.email.verify.notice', [], false));
    expect(test()->guest->fresh()->name)->toBe('Original Name');
});

test('an unverified customer does not get the order confirmation', function () {
    registerForGuestEmail();

    $response = withoutMiddleware(tenantMiddleware())->get(route('order.confirmation', test()->order, false));

    $response->assertRedirect(route('order.verify.show', test()->order, false));
});

test('an unverified customer cannot modify the order', function () {
    registerForGuestEmail();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('order.modify', test()->order, false), [
            'items' => [['order_item_id' => test()->item->id, 'quantity' => 4]],
        ]);

    $response->assertRedirect(route('order.verify.show', test()->order, false));
    expect(test()->item->fresh()->quantity)->toBe(2);
});

test('after verifying the email the customer sees the order history and the confirmation', function () {
    registerForGuestEmail();
    test()->guest->markEmailAsVerified();
    auth()->forgetGuards();

    $orders = withoutMiddleware(tenantMiddleware())->get(route('account.orders', [], false));
    $confirmation = withoutMiddleware(tenantMiddleware())->get(route('order.confirmation', test()->order, false));

    $orders->assertOk();
    $orders->assertViewHas('orders', fn ($orders) => $orders->total() === 1);
    $confirmation->assertOk();
});

test('the verify notice, resend and logout stay reachable while unverified', function () {
    registerForGuestEmail();

    $notice = withoutMiddleware(tenantMiddleware())->get(route('account.email.verify.notice', [], false));
    $notice->assertOk();

    $resend = withoutMiddleware(tenantMiddleware())->post(route('account.email.verify.send', [], false));
    $resend->assertSessionHas('status');

    $logout = withoutMiddleware(tenantMiddleware())->post(route('account.logout', [], false));
    $logout->assertRedirect();
    expect(auth('customer')->check())->toBeFalse();
});
