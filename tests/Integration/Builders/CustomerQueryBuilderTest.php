<?php

use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\ValueObjects\DateRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

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

test('withPaidOrderMetrics aggregates only active paid orders in the date range', function () {
    $customer = Customer::factory()->create();

    Order::factory()->for($customer)->paid()->create([
        'delivery_date' => '2026-03-10',
        'total' => 100,
    ]);
    Order::factory()->for($customer)->unpaid()->create([
        'delivery_date' => '2026-03-11',
        'total' => 500,
    ]);
    Order::factory()->for($customer)->cancelled()->paid()->create([
        'delivery_date' => '2026-03-12',
        'total' => 700,
    ]);
    Order::factory()->for($customer)->paid()->create([
        'delivery_date' => '2026-04-01',
        'total' => 900,
    ]);

    $result = Customer::query()
        ->withPaidOrderMetrics(DateRange::forMonth(2026, 3))
        ->findOrFail($customer->id);

    expect((int) $result->total_spend)->toBe(10_000)
        ->and($result->order_count)->toBe(1);
});

test('withRfmMetrics projects lifetime paid order metrics', function () {
    $customer = Customer::factory()->create();
    Order::factory()->for($customer)->paid()->create([
        'delivery_date' => '2020-01-01',
        'total' => 10,
    ]);
    Order::factory()->for($customer)->cancelled()->paid()->create([
        'delivery_date' => '2026-09-01',
        'total' => 15,
    ]);
    Order::factory()->for($customer)->unpaid()->create([
        'delivery_date' => '2026-09-03',
        'total' => 100,
    ]);

    $customerWithoutPaidOrders = Customer::factory()->create();
    Order::factory()->for($customerWithoutPaidOrders)->unpaid()->create();

    $results = Customer::query()->withRfmMetrics()->get();
    $result = $results->sole();

    expect($result->is($customer))->toBeTrue()
        ->and(Arr::integer($result->getAttributes(), 'frequency'))->toBe(2)
        ->and(Arr::integer($result->getAttributes(), 'monetary_cents'))->toBe(2_500)
        ->and(Date::parse((string) $result->getAttribute('last_order_at'))->toDateString())->toBe('2026-09-01');
});
