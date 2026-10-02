<?php

use App\Actions\Customers\RegisterCustomer;
use App\Models\Customers\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

function registrationData(array $overrides = []): array
{
    return [
        'name' => 'New Registrant',
        'email' => 'new@example.com',
        'phone' => '5559999',
        'password' => 'password123',
        ...$overrides,
    ];
}

test('creates a new unverified customer with the submitted details', function () {
    $customer = app(RegisterCustomer::class)(registrationData());

    expect($customer->exists)->toBeTrue()
        ->and($customer->name)->toBe('New Registrant')
        ->and($customer->phone)->toBe('5559999')
        ->and($customer->password)->not->toBeNull()
        ->and($customer->email_verified_at)->toBeNull();
});

test('claiming a guest customer keeps its existing name and phone', function () {
    $guest = Customer::factory()->create(['email' => 'new@example.com', 'name' => 'Original Name', 'phone' => '5550100']);

    $customer = app(RegisterCustomer::class)(registrationData());

    expect($customer->is($guest))->toBeTrue()
        ->and($customer->name)->toBe('Original Name')
        ->and($customer->phone)->toBe('5550100')
        ->and($customer->password)->not->toBeNull()
        ->and(Customer::query()->count())->toBe(1);
});

test('claiming a guest customer matches an email that differs only by case', function (string $submitted) {
    $guest = Customer::factory()->create(['email' => 'new@example.com', 'name' => 'Original Name']);

    $customer = app(RegisterCustomer::class)(registrationData(['email' => $submitted]));

    expect($customer->is($guest))->toBeTrue()
        ->and($customer->email)->toBe('new@example.com')
        ->and(Customer::query()->count())->toBe(1);
})->with([
    'mixed case' => 'New@Example.com',
    'padded' => ' new@example.com ',
]);

test('claiming a guest customer fills a missing phone', function () {
    Customer::factory()->create(['email' => 'new@example.com', 'name' => 'Original Name', 'phone' => null]);

    $customer = app(RegisterCustomer::class)(registrationData());

    expect($customer->name)->toBe('Original Name')
        ->and($customer->phone)->toBe('5559999');
});

test('claiming a guest customer clears any previous verification', function () {
    Customer::factory()->verified()->create(['email' => 'new@example.com']);

    $customer = app(RegisterCustomer::class)(registrationData());

    expect($customer->email_verified_at)->toBeNull();
});
