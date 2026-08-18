<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('newThisWeek returns customers created this week', function () {
    $thisWeek = Customer::factory()->create(['created_at' => now()]);
    $lastMonth = Customer::factory()->create(['created_at' => now()->subMonth()]);

    $results = Customer::query()->newThisWeek()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($thisWeek->id);
});

test('atRisk returns customers with orders but none recent', function () {
    $atRisk = Customer::factory()->create();
    $order = Order::factory()->recycle($atRisk)->create();
    Order::query()->where('id', $order->id)->update(['created_at' => now()->subDays(45)]);

    $active = Customer::factory()->create();
    Order::factory()->recycle($active)->create();

    $results = Customer::query()->atRisk(30)->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($atRisk->id);
});

test('forEmail filters customers by email', function () {
    $target = Customer::factory()->create(['email' => 'alice@example.com']);
    Customer::factory()->create(['email' => 'bob@example.com']);

    $results = Customer::query()->forEmail('alice@example.com')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($target->id);
});

test('forReferralCode filters customers by referral code', function () {
    $target = Customer::factory()->create(['referral_code' => 'ALICE-7']);
    Customer::factory()->create(['referral_code' => 'BOB-3']);

    $results = Customer::query()->forReferralCode('ALICE-7')->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($target->id);
});
