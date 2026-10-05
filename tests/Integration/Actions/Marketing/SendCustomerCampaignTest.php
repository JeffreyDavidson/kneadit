<?php

use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Enums\Orders\PaymentStatus;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Engagement\CustomerCampaignLog;
use App\Models\Orders\Order;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Mail::fake();
});

test('queues mail to all recipients and marks campaign sent', function () {
    Customer::factory()->count(2)->create()->each(function (Customer $c): void {
        Order::factory()->for($c)->paid()->create([
            'delivery_date' => now()->subDays(5)->format('Y-m-d'),
            'payment_status' => PaymentStatus::Paid,
        ]);
    });

    $campaign = CustomerCampaign::factory()->create(['target_segment' => 'all']);

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(2)
        ->and($campaign->fresh()->status)->toBe(CustomerCampaignStatus::Sent)
        ->and($campaign->fresh()->sent_at)->not->toBeNull()
        ->and($campaign->fresh()->recipient_count)->toBe(2);

    Mail::assertQueued(CustomerCampaignMail::class, 2);
});

test('refuses to re-send a campaign that is already Sent', function () {
    $campaign = CustomerCampaign::factory()->sent(50)->create();

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(0);
    Mail::assertNothingQueued();
    // Recipient count unchanged.
    expect($campaign->fresh()->recipient_count)->toBe(50);
});

test('refuses to send a campaign claimed by another worker', function () {
    $campaign = CustomerCampaign::factory()->sending()->create();

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(0);
    Mail::assertNothingQueued();
});

test('queues nothing when there are no recipients in the segment', function () {
    $campaign = CustomerCampaign::factory()->create(['target_segment' => 'all']);

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(0)
        ->and($campaign->fresh()->status)->toBe(CustomerCampaignStatus::Sent)
        ->and($campaign->fresh()->recipient_count)->toBe(0);
    Mail::assertNothingQueued();
});

test('skips recipients that already have a log row and emails only the rest', function () {
    $customers = Customer::factory()->count(3)->create();
    $customers->each(fn (Customer $customer) => Order::factory()->for($customer)->paid()->create([
        'delivery_date' => now()->subDays(5)->format('Y-m-d'),
    ]));
    $campaign = CustomerCampaign::factory()->create(['target_segment' => 'all']);
    CustomerCampaignLog::factory()->for($campaign, 'campaign')->create(['customer_email' => $customers[0]->email]);

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(2)
        ->and($campaign->fresh()->recipient_count)->toBe(3)
        ->and($campaign->logs()->count())->toBe(3);
    Mail::assertQueued(CustomerCampaignMail::class, 2);
    Mail::assertNotQueued(CustomerCampaignMail::class, fn (CustomerCampaignMail $mail): bool => $mail->hasTo($customers[0]->email));
});

test('the database refuses a second log row for the same campaign and email', function () {
    $campaign = CustomerCampaign::factory()->create();
    CustomerCampaignLog::factory()->for($campaign, 'campaign')->create(['customer_email' => 'dup@example.com']);

    expect(fn () => CustomerCampaignLog::factory()->for($campaign, 'campaign')->create(['customer_email' => 'dup@example.com']))
        ->toThrow(UniqueConstraintViolationException::class);
});
