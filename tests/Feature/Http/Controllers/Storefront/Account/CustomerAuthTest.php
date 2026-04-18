<?php

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('register creates a customer, hashes the password, and signs them in', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.register', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '555-0100',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('account.dashboard', [], false));

    $customer = Customer::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($customer->name)->toBe('Jane Doe')
        ->and(Hash::check('password123', $customer->password))->toBeTrue()
        ->and(auth('customer')->id())->toBe($customer->id);
});

test('register rejects an email that already has a customer', function () {
    Customer::factory()->create(['email' => 'taken@example.com']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.register', [], false), [
            'name' => 'Someone Else',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertSessionHasErrors(['email']);
    expect(Customer::query()->count())->toBe(1);
});

test('login authenticates a customer with valid credentials', function () {
    $customer = Customer::factory()->withPassword('password123')->create(['email' => 'jane@example.com']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.login', [], false), [
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

    $response->assertRedirect(route('account.dashboard', [], false));
    expect(auth('customer')->id())->toBe($customer->id);
});

test('login rejects invalid credentials', function () {
    Customer::factory()->withPassword('password123')->create(['email' => 'jane@example.com']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('account.login', [], false), [
            'email' => 'jane@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors(['email']);
    expect(auth('customer')->check())->toBeFalse();
});

test('dashboard redirects guests to the login page', function () {
    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('account.dashboard', [], false));

    $response->assertRedirect(route('account.login.show', [], false));
});

test('dashboard shows the signed-in customer name', function () {
    $customer = Customer::factory()->withPassword()->create(['name' => 'Ada Lovelace']);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get(route('account.dashboard', [], false));

    $response->assertOk()->assertSee('Ada Lovelace');
});

test('logout ends the customer session', function () {
    $customer = Customer::factory()->withPassword()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->post(route('account.logout', [], false));

    $response->assertRedirect(route('storefront.menu', [], false));
    expect(auth('customer')->check())->toBeFalse();
});
