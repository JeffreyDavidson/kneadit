<?php

use App\Actions\Platform\ScheduleEmailCampaign;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Models\Engagement\EmailCampaign;

beforeEach(fn () => setUpCentralTest());

test('it schedules an email campaign', function () {
    $campaign = EmailCampaign::factory()->create([
        'status' => EmailCampaignStatus::Draft,
        'scheduled_at' => null,
    ]);

    $scheduledAt = now()->addDays(3);

    resolve(ScheduleEmailCampaign::class)($campaign, $scheduledAt);

    $campaign->refresh();
    $persistedSchedule = $campaign->scheduled_at;

    if ($persistedSchedule === null) {
        throw new RuntimeException('Expected the campaign to be scheduled.');
    }

    expect($campaign->status)->toBe(EmailCampaignStatus::Scheduled)
        ->and($persistedSchedule->toDateTimeString())->toBe($scheduledAt->toDateTimeString());
});
