<?php

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
