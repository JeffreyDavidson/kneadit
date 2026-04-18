<?php

use App\Models\Customers\Customer;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Notifications\Customers\CustomerVerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('verification email is sent after registration', function () {
    Notification::fake();

    withoutMiddleware(tenantMiddleware())
        ->post(route('account.register', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $customer = Customer::query()->where('email', 'jane@example.com')->firstOrFail();

    Notification::assertSentTo($customer, CustomerVerifyEmailNotification::class);
});

test('a signed verification URL marks the email verified and redirects to the dashboard', function () {
    Event::fake();
    $customer = Customer::factory()->withPassword()->create(['email_verified_at' => null]);

    $url = URL::temporarySignedRoute(
        'account.email.verify',
        now()->addMinutes(60),
        ['id' => $customer->id, 'hash' => sha1($customer->email)],
    );

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get($url);

    $response->assertRedirect(route('account.dashboard', [], false));
    expect($customer->fresh()->hasVerifiedEmail())->toBeTrue();
    Event::assertDispatched(Verified::class);
});

test('an unsigned verification URL is rejected', function () {
    $customer = Customer::factory()->withPassword()->create(['email_verified_at' => null]);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get(route('account.email.verify', ['id' => $customer->id, 'hash' => sha1($customer->email)], false));

    $response->assertStatus(403);
    expect($customer->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('a verification URL with a mismatched id is rejected', function () {
    $customer = Customer::factory()->withPassword()->create(['email_verified_at' => null]);

    $url = URL::temporarySignedRoute(
        'account.email.verify',
        now()->addMinutes(60),
        ['id' => 9999, 'hash' => sha1($customer->email)],
    );

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get($url);

    $response->assertStatus(403);
});

test('the resend route sends another verification email', function () {
    Notification::fake();
    $customer = Customer::factory()->withPassword()->create(['email_verified_at' => null]);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->post(route('account.email.verify.send', [], false));

    $response->assertRedirect();
    Notification::assertSentTo($customer, CustomerVerifyEmailNotification::class);
});

test('registering with a guest customer email claims their record and keeps their orders', function () {
    $product = Product::factory()->create();
    $guest = Customer::factory()->create(['email' => 'jane@example.com', 'name' => 'J. Doe', 'password' => null]);
    Order::factory()->for($guest)->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.register', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('account.email.verify.notice', [], false));

    expect(Customer::query()->count())->toBe(1);

    $claimed = Customer::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($claimed->id)->toBe($guest->id)
        ->and($claimed->name)->toBe('Jane Doe')
        ->and($claimed->password)->not->toBeNull()
        ->and($claimed->email_verified_at)->toBeNull()
        ->and($claimed->orders)->toHaveCount(1);
});
