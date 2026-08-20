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
    $log = EmailCampaignLog::factory()->createOne([
        'email' => 'test@example.com',
        'tenant_id' => 'test-bakery',
    ]);

    expect(EmailCampaignLog::query()->where('email', 'test@example.com')->firstOrFail())
        ->not->toBeNull();
});

test('status is cast to EmailDeliveryStatus enum', function () {
    $log = EmailCampaignLog::factory()->createOne([
        'status' => EmailDeliveryStatus::Sent,
        'tenant_id' => 'test-bakery',
    ]);

    $log->refresh();

    expect($log->status)->toBeInstanceOf(EmailDeliveryStatus::class)
        ->and($log->status)->toBe(EmailDeliveryStatus::Sent);
});

test('sent at is cast to datetime', function () {
    $log = EmailCampaignLog::factory()->createOne([
        'sent_at' => '2026-01-15 10:00:00',
        'tenant_id' => 'test-bakery',
    ]);

    $log->refresh();

    expect($log->sent_at)->toBeInstanceOf(Carbon::class);
});

test('opened at is cast to datetime', function () {
    $log = EmailCampaignLog::factory()->createOne([
        'opened_at' => '2026-01-15 14:00:00',
        'tenant_id' => 'test-bakery',
    ]);

    $log->refresh();

    expect($log->opened_at)->toBeInstanceOf(Carbon::class);
});

test('campaign relationship returns the parent campaign', function () {
    $campaign = EmailCampaign::factory()->createOne();
    $log = EmailCampaignLog::factory()->createOne([
        'campaign_id' => $campaign->id,
        'tenant_id' => 'test-bakery',
    ]);

    $parent = $log->campaign;
    expect($parent->id)->toBe($campaign->id);
});
