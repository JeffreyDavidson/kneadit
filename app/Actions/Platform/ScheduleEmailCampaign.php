<?php

namespace App\Actions\Platform;

use App\Enums\Marketing\EmailCampaignStatus;
use App\Models\Engagement\EmailCampaign;
use Illuminate\Support\Carbon;

class ScheduleEmailCampaign
{
    public function __invoke(EmailCampaign $campaign, Carbon|string $scheduledAt): void
    {
        $campaign->update([
            'status' => EmailCampaignStatus::Scheduled,
            'scheduled_at' => $scheduledAt,
        ]);
    }
}
