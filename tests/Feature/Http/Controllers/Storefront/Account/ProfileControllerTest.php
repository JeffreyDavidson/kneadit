<?php

use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('redirects unauthenticated visitors away from the profile form', function () {
    $response = withoutMiddleware(tenantMiddleware())->get('/account/profile');

    $response->assertRedirect();
});

test('shows the profile form for an authenticated customer', function () {
    $customer = Customer::factory()->create(['name' => 'Alice']);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->get('/account/profile');

    $response->assertOk();
    $response->assertSee('Alice');
    $response->assertSee('Email:');
});

test('updates name + phone + birthday + address', function () {
    $customer = Customer::factory()->create(['name' => 'Old Name']);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->post('/account/profile', [
            'name' => 'New Name',
            'phone' => '555-1234',
            'birthday' => '1990-04-15',
            'address' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62701',
        ]);

    $response->assertRedirect(route('account.profile.show'));
    $response->assertSessionHas('status');

    $customer->refresh();
    expect($customer->name)->toBe('New Name')
        ->and($customer->city)->toBe('Springfield')
        ->and($customer->birthday?->format('Y-m-d'))->toBe('1990-04-15');
});

test('rejects empty name', function () {
    $customer = Customer::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->post('/account/profile', [
            'name' => '',
        ]);

    $response->assertSessionHasErrors(['name']);
});

test('does not allow changing the email address via the profile form', function () {
    $customer = Customer::factory()->create(['email' => 'original@example.com']);

    withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->post('/account/profile', [
            'name' => 'New Name',
            'email' => 'evil@example.com',
        ]);

    $customer->refresh();

    expect($customer->email)->toBe('original@example.com');
});

test('rejects future birthday', function () {
    $customer = Customer::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($customer, 'customer')
        ->post('/account/profile', [
            'name' => 'Test',
            'birthday' => now()->addYear()->format('Y-m-d'),
        ]);

    $response->assertSessionHasErrors(['birthday']);
});
