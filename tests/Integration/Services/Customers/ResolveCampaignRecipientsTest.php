<?php

use App\Enums\Customers\RfmSegment;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Services\Customers\ResolveCampaignRecipients;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

/** Helper: stamp a customer with N paid orders totaling $totalDollars, most recent $recencyDays ago. */
function makeRecipientCustomer(int $frequency, float $totalDollars, int $recencyDays, string $email): Customer
{
    $customer = Customer::factory()->create(['email' => $email]);
    $perOrder = $frequency > 0 ? $totalDollars / $frequency : 0;
    for ($i = 0; $i < $frequency; $i++) {
        $days = $i === 0 ? $recencyDays : $recencyDays + (($i + 1) * 30);
        Order::factory()->for($customer)->paid()->create([
            'delivery_date' => now()->subDays($days)->format('Y-m-d'),
            'total' => $perOrder,
            'subtotal' => $perOrder,
            'payment_status' => PaymentStatus::Paid,
        ]);
    }

    return $customer;
}

test('all returns every customer with at least one paid order', function () {
    makeRecipientCustomer(1, 20.0, 10, 'a@example.com');
    makeRecipientCustomer(1, 40.0, 20, 'b@example.com');
    Customer::factory()->create(['email' => 'no-orders@example.com']);

    $emails = resolve(ResolveCampaignRecipients::class)('all')->pluck('email')->sort()->values();

    expect($emails->toArray())->toBe(['a@example.com', 'b@example.com']);
});

test('a specific segment returns only matching customers', function () {
    // Champion: recent, frequent, big spend
    makeRecipientCustomer(frequency: 5, totalDollars: 600.0, recencyDays: 5, email: 'champ@example.com');
    // New: recent, low frequency
    makeRecipientCustomer(frequency: 1, totalDollars: 40.0, recencyDays: 5, email: 'new@example.com');

    $champs = resolve(ResolveCampaignRecipients::class)(RfmSegment::Champions->value);
    $news = resolve(ResolveCampaignRecipients::class)(RfmSegment::New->value);

    expect($champs->pluck('email')->toArray())->toBe(['champ@example.com'])
        ->and($news->pluck('email')->toArray())->toBe(['new@example.com']);
});

test('unknown segment string returns an empty collection', function () {
    makeRecipientCustomer(1, 20.0, 10, 'a@example.com');

    expect(resolve(ResolveCampaignRecipients::class)('not-a-real-segment')->isEmpty())->toBeTrue();
});

test('excludes customers with no paid orders', function () {
    Customer::factory()->create(['email' => 'unpaid@example.com']);

    expect(resolve(ResolveCampaignRecipients::class)('all')->isEmpty())->toBeTrue();
});
