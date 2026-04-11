<?php

use App\Enums\Marketing\EmailDeliveryStatus;
use App\Models\Engagement\EmailCampaign;
use App\Models\Engagement\EmailCampaignLog;
use Carbon\Carbon;

beforeEach(function () {
    setUpCentralTest();
    test()->tenant = createTenant();
});

test('can create an email campaign log', function () {
    $log = EmailCampaignLog::factory()->create([
        'email' => 'test@example.com',
        'tenant_id' => test()->tenant->id,
    ]);

    expect(EmailCampaignLog::query()->where('email', 'test@example.com')->first())
        ->not->toBeNull();
});

test('status is cast to EmailDeliveryStatus enum', function () {
    $log = EmailCampaignLog::factory()->create([
        'status' => EmailDeliveryStatus::Sent,
        'tenant_id' => test()->tenant->id,
    ]);

    $log->refresh();

    expect($log->status)->toBeInstanceOf(EmailDeliveryStatus::class)
        ->and($log->status)->toBe(EmailDeliveryStatus::Sent);
});

test('sent at is cast to datetime', function () {
    $log = EmailCampaignLog::factory()->create([
        'sent_at' => '2026-01-15 10:00:00',
        'tenant_id' => test()->tenant->id,
    ]);

    $log->refresh();

    expect($log->sent_at)->toBeInstanceOf(Carbon::class);
});

test('opened at is cast to datetime', function () {
    $log = EmailCampaignLog::factory()->create([
        'opened_at' => '2026-01-15 14:00:00',
        'tenant_id' => test()->tenant->id,
    ]);

    $log->refresh();

    expect($log->opened_at)->toBeInstanceOf(Carbon::class);
});

test('campaign relationship returns the parent campaign', function () {
    $campaign = EmailCampaign::factory()->create();
    $log = EmailCampaignLog::factory()->create([
        'campaign_id' => $campaign->id,
        'tenant_id' => test()->tenant->id,
    ]);

    expect($log->campaign->id)->toBe($campaign->id);
});
