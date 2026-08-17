<?php

use App\Enums\Marketing\CustomerCampaignStatus;
use App\Models\Engagement\CustomerCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('saving a draft with scheduled_at promotes status to Scheduled', function () {
    $campaign = CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Draft,
        'scheduled_at' => now()->addDay(),
    ]);

    expect($campaign->refresh()->status)->toBe(CustomerCampaignStatus::Scheduled);
});

test('clearing scheduled_at on a Scheduled campaign demotes it to Draft', function () {
    $campaign = CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Draft,
        'scheduled_at' => now()->addDay(),
    ]);
    expect($campaign->refresh()->status)->toBe(CustomerCampaignStatus::Scheduled);

    $campaign->forceFill(['scheduled_at' => null])->save();

    expect($campaign->refresh()->status)->toBe(CustomerCampaignStatus::Draft);
});

test('saving without scheduled_at leaves a draft as Draft', function () {
    $campaign = CustomerCampaign::factory()->create();

    expect($campaign->refresh()->status)->toBe(CustomerCampaignStatus::Draft)
        ->and($campaign->refresh()->scheduled_at)->toBeNull();
});

test('does not change status of an already-Sent campaign even if scheduled_at is set', function () {
    $campaign = CustomerCampaign::factory()->sent()->create([
        'scheduled_at' => now()->addDay(),
    ]);

    expect($campaign->refresh()->status)->toBe(CustomerCampaignStatus::Sent);
});
