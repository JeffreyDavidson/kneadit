<?php

namespace App\Actions\Platform;

use App\Enums\EmailCampaignStatus;
use App\Models\EmailCampaign;
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
