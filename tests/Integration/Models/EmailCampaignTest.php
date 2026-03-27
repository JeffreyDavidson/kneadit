<?php

use App\Enums\EmailCampaignStatus;
use App\Models\Customer;
use App\Models\EmailCampaign;
use Carbon\Carbon;

beforeEach(fn () => setUpCentralTest());

test('can create campaign', function () {
    $campaign = EmailCampaign::query()->create([
        'name' => 'Test Campaign',
        'subject' => 'Newsletter',
        'body' => 'Content here',
        'status' => EmailCampaignStatus::Draft,
    ]);

    expect(EmailCampaign::query()->where('subject', 'Newsletter')->first())->not->toBeNull();
});

test('sent at is cast to datetime', function () {
    $campaign = EmailCampaign::query()->create([
        'name' => 'Test',
        'subject' => 'Test',
        'body' => 'Body',
        'status' => EmailCampaignStatus::Sent,
        'sent_at' => '2026-01-01 12:00:00',
    ]);

    $campaign->refresh();
    expect($campaign->sent_at)->toBeInstanceOf(Carbon::class);
});

test('status can be updated to sent', function () {
    $campaign = EmailCampaign::query()->create(['name' => 'T', 'subject' => 'T', 'body' => 'B', 'status' => EmailCampaignStatus::Draft]);
    $campaign->update(['status' => EmailCampaignStatus::Sent, 'sent_at' => now()]);

    expect($campaign->fresh()->status)->toBe(EmailCampaignStatus::Sent);
});

test('recipient count stored correctly', function () {
    Customer::query()->create(['name' => 'Customer 1', 'email' => 'c1@example.com']);
    Customer::query()->create(['name' => 'Customer 2', 'email' => 'c2@example.com']);
    Customer::query()->create(['name' => 'Customer 3', 'email' => 'c3@example.com']);

    $count = Customer::query()->whereNotNull('email')->count();

    $campaign = EmailCampaign::query()->create([
        'subject' => 'To All',
        'body' => 'Hello everyone',
        'status' => EmailCampaignStatus::Sent,
        'sent_at' => now(),
        'recipient_count' => $count,
    ]);

    expect($campaign->recipient_count)->toBe(3);
});
