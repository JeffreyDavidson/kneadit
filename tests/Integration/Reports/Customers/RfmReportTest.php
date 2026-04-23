<?php

use App\Enums\Customers\RfmSegment;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Reports\Customers\RfmReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

/**
 * Helper: stamp a customer with N paid orders totaling $totalDollars,
 * with the most recent order $recencyDays days ago.
 */
function makeRfmCustomer(int $frequency, float $totalDollars, int $recencyDays, string $email = 'x@example.com'): Customer
{
    $customer = Customer::factory()->create(['email' => $email]);

    $perOrder = $frequency > 0 ? $totalDollars / $frequency : 0;

    for ($i = 0; $i < $frequency; $i++) {
        $days = $i === 0 ? $recencyDays : $recencyDays + (($i + 1) * 30);
        Order::factory()
            ->for($customer)
            ->paid()
            ->create([
                'delivery_date' => now()->subDays($days)->format('Y-m-d'),
                'total' => $perOrder,
                'subtotal' => $perOrder,
                'payment_status' => PaymentStatus::Paid,
            ]);
    }

    return $customer;
}

test('empty set returns 0 total and all segments at 0', function () {
    $result = resolve(RfmReport::class)->generate();

    expect($result['total'])->toBe(0);
    foreach ($result['segments'] as $row) {
        expect($row['count'])->toBe(0);
        expect($row['sampleCustomers'])->toBe([]);
    }
});

test('classifies a frequent big spender with a recent order as Champion', function () {
    makeRfmCustomer(frequency: 5, totalDollars: 600.0, recencyDays: 5, email: 'champ@example.com');

    $result = resolve(RfmReport::class)->generate();

    expect($result['segments'][RfmSegment::Champions->value]['count'])->toBe(1);
    expect($result['segments'][RfmSegment::Champions->value]['sampleCustomers'][0]['email'])->toBe('champ@example.com');
});

test('classifies a consistent moderate-spender within 60 days as Loyal', function () {
    makeRfmCustomer(frequency: 3, totalDollars: 250.0, recencyDays: 40, email: 'loyal@example.com');

    $result = resolve(RfmReport::class)->generate();

    expect($result['segments'][RfmSegment::Loyal->value]['count'])->toBe(1);
});

test('classifies a valuable customer cold for 90 days as AtRisk', function () {
    makeRfmCustomer(frequency: 4, totalDollars: 300.0, recencyDays: 90, email: 'risk@example.com');

    $result = resolve(RfmReport::class)->generate();

    expect($result['segments'][RfmSegment::AtRisk->value]['count'])->toBe(1);
});

test('classifies a low-frequency recent customer as New', function () {
    makeRfmCustomer(frequency: 1, totalDollars: 40.0, recencyDays: 5, email: 'new@example.com');

    $result = resolve(RfmReport::class)->generate();

    expect($result['segments'][RfmSegment::New->value]['count'])->toBe(1);
});

test('classifies a 200-day-inactive customer as Hibernating', function () {
    makeRfmCustomer(frequency: 2, totalDollars: 80.0, recencyDays: 200, email: 'hiber@example.com');

    $result = resolve(RfmReport::class)->generate();

    expect($result['segments'][RfmSegment::Hibernating->value]['count'])->toBe(1);
});

test('excludes customers with no paid orders', function () {
    Customer::factory()->create(); // no orders at all

    $result = resolve(RfmReport::class)->generate();

    expect($result['total'])->toBe(0);
});

test('limits sample customers to 5 per segment', function () {
    for ($i = 0; $i < 7; $i++) {
        makeRfmCustomer(frequency: 1, totalDollars: 20.0, recencyDays: 5, email: "new{$i}@example.com");
    }

    $result = resolve(RfmReport::class)->generate();

    expect($result['segments'][RfmSegment::New->value]['count'])->toBe(7);
    expect($result['segments'][RfmSegment::New->value]['sampleCustomers'])->toHaveCount(5);
});
