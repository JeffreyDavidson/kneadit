<?php

use App\Builders\Engagement\CustomerCampaignQueryBuilder;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Models\Engagement\CustomerCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

function customerCampaignQuery(): CustomerCampaignQueryBuilder
{
    return CustomerCampaign::query();
}

beforeEach(fn () => setUpTenantTest());

test('scheduled returns only scheduled campaigns', function () {
    CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);
    CustomerCampaign::factory()->sent()->create();

    $campaigns = customerCampaignQuery()->scheduled()->get();

    expect($campaigns)->toHaveCount(1);
});

test('due returns scheduled campaigns whose scheduled time has arrived', function () {
    $due = CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Scheduled,
        'scheduled_at' => now()->subMinute(),
    ]);
    CustomerCampaign::factory()->create([
        'status' => CustomerCampaignStatus::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);

    $campaigns = customerCampaignQuery()->due()->get();

    expect($campaigns)->toHaveCount(1)
        ->and($campaigns->firstOrFail()->is($due))->toBeTrue();
});
