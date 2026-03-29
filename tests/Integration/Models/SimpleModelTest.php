<?php

use App\Models\Customer;
use App\Models\CustomerNote;
use App\Models\CustomerProfile;
use App\Models\CustomerReminder;
use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('customer note belongs to customer', function () {
    $customer = Customer::factory()->create();
    $user = User::factory()->create();
    $note = CustomerNote::factory()->recycle($customer)->recycle($user)->create();

    expect($note->customer->id)->toBe($customer->id);
});

test('customer profile belongs to customer', function () {
    $customer = Customer::factory()->create();
    $profile = CustomerProfile::factory()->recycle($customer)->create();

    expect($profile->customer->id)->toBe($customer->id);
});

test('stock adjustment belongs to ingredient', function () {
    $ingredient = Ingredient::factory()->create();
    $adjustment = StockAdjustment::factory()->recycle($ingredient)->create();

    expect($adjustment->ingredient->id)->toBe($ingredient->id);
});

test('customer reminder belongs to customer', function () {
    $customer = Customer::factory()->create();
    $reminder = CustomerReminder::factory()->recycle($customer)->create();

    expect($reminder->customer->id)->toBe($customer->id);
});
