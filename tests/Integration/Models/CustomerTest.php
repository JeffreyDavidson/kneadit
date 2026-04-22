<?php

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Customers\CustomerPhoto;
use App\Models\Customers\CustomerProfile;
use App\Models\Customers\CustomerReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
});

test('customer has customer reminders relationship', function () {
    $customer = Customer::factory()->create();

    CustomerReminder::factory()->for($customer)->create();

    expect($customer->customerReminders)->toHaveCount(1);
});

test('customer has customer photos relationship via email', function () {
    $customer = Customer::factory()->create(['email' => 'photo@test.com']);

    CustomerPhoto::factory()->create(['customer_email' => 'photo@test.com']);

    expect($customer->customerPhotos)->toHaveCount(1);
});

test('customer has customer favorites relationship via email', function () {
    $customer = Customer::factory()->create(['email' => 'fav@test.com']);

    CustomerFavorite::factory()->create(['customer_email' => 'fav@test.com']);

    expect($customer->customerFavorites)->toHaveCount(1);
});

test('customer has customer profile relationship', function () {
    $customer = Customer::factory()->create();

    CustomerProfile::factory()->for($customer)->create();

    expect($customer->customerProfile)->toBeInstanceOf(CustomerProfile::class);
});

test('address object aggregates the four address columns into an Address value object', function () {
    $customer = Customer::factory()->create([
        'address' => '123 Main St',
        'city' => 'Springfield',
        'state' => 'IL',
        'zip' => '62704',
    ]);

    expect($customer->address_object)
        ->street->toBe('123 Main St')
        ->city->toBe('Springfield')
        ->state->toBe('IL')
        ->zip->toBe('62704');
});
