<?php

use App\Enums\Marketing\EmailCampaignStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\EmailCampaign;
use Carbon\Carbon;

beforeEach(fn () => setUpCentralTest());

test('can create campaign', function () {
    $campaign = EmailCampaign::factory()->create([
        'subject' => 'Newsletter',
    ]);

    expect(EmailCampaign::query()->where('subject', 'Newsletter')->firstOrFail())->not->toBeNull();
});

test('sent at is cast to datetime', function () {
    $campaign = EmailCampaign::factory()->sent()->create([
        'sent_at' => '2026-01-01 12:00:00',
    ]);

    $campaign->refresh();
    expect($campaign->sent_at)->toBeInstanceOf(Carbon::class);
});

test('status can be updated to sent', function () {
    $campaign = EmailCampaign::factory()->create();
    $campaign->update(['status' => EmailCampaignStatus::Sent, 'sent_at' => now()]);

    expect($campaign->refresh()->status)->toBe(EmailCampaignStatus::Sent);
});

test('recipient count stored correctly', function () {
    Customer::factory()->count(3)->create();

    $count = Customer::query()->whereNotNull('email')->count();

    $campaign = EmailCampaign::factory()->sent()->create([
        'sent_at' => now(),
        'recipient_count' => $count,
    ]);

    expect($campaign->recipient_count)->toBe(3);
});
