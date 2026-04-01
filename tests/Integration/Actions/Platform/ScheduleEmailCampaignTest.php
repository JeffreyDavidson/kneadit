<?php

use App\Actions\Platform\ScheduleEmailCampaign;
use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;

beforeEach(fn () => setUpCentralTest());

test('it schedules an email campaign', function () {
    $campaign = EmailCampaign::factory()->create([
        'status' => EmailCampaignStatus::Draft,
        'scheduled_at' => null,
    ]);

    $scheduledAt = now()->addDays(3);

    resolve(ScheduleEmailCampaign::class)($campaign, $scheduledAt);

    $campaign->refresh();

    expect($campaign->status)->toBe(EmailCampaignStatus::Scheduled)
        ->and($campaign->scheduled_at->toDateTimeString())->toBe($scheduledAt->toDateTimeString());
});
