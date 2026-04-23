<?php

use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Enums\Orders\PaymentStatus;
use App\Mail\Customers\CustomerCampaignMail;
use App\Models\Customers\Customer;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

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

    expect($sent)->toBe(2);
    expect($campaign->fresh()->status)->toBe(CustomerCampaignStatus::Sent);
    expect($campaign->fresh()->sent_at)->not->toBeNull();
    expect($campaign->fresh()->recipient_count)->toBe(2);

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

test('queues nothing when there are no recipients in the segment', function () {
    $campaign = CustomerCampaign::factory()->create(['target_segment' => 'all']);

    $sent = resolve(SendCustomerCampaign::class)($campaign);

    expect($sent)->toBe(0);
    expect($campaign->fresh()->status)->toBe(CustomerCampaignStatus::Sent);
    expect($campaign->fresh()->recipient_count)->toBe(0);
    Mail::assertNothingQueued();
});
