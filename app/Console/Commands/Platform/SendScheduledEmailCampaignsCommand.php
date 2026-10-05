<?php

namespace App\Console\Commands\Platform;

use App\Actions\Platform\SendEmailCampaign;
use App\Enums\Marketing\EmailCampaignStatus;
use App\Models\Engagement\EmailCampaign;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('platform:send-scheduled-campaigns')]
#[Description('Send any platform email campaigns whose scheduled_at has arrived')]
class SendScheduledEmailCampaignsCommand extends Command
{
    public function handle(SendEmailCampaign $send): int
    {
        $this->resetStaleCampaigns();

        $due = EmailCampaign::query()
            ->where('status', EmailCampaignStatus::Scheduled)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        $failures = 0;

        foreach ($due as $campaign) {
            // Claim the campaign atomically so two overlapping runs can't both send it.
            $claimed = EmailCampaign::query()
                ->whereKey($campaign->getKey())
                ->where('status', EmailCampaignStatus::Scheduled)
                ->update(['status' => EmailCampaignStatus::Sending]);

            if ($claimed !== 1) {
                continue;
            }

            try {
                $send($campaign->refresh());
                $this->info("Campaign #{$campaign->id} sent to {$campaign->recipient_count} recipient(s)");
            } catch (\Throwable $e) {
                $failures++;
                report($e);
                $this->error("Campaign #{$campaign->id} failed: {$e->getMessage()}");
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * A send that died partway is put back to Scheduled and resumes; the sender
     * skips owners that already have a log row. A campaign sent from "Send now"
     * has no scheduled_at, so give it one or it would never be due.
     */
    private function resetStaleCampaigns(): void
    {
        $stale = EmailCampaign::query()
            ->where('status', EmailCampaignStatus::Sending)
            ->where('updated_at', '<=', now()->subHour());

        $stale->clone()
            ->whereNull('scheduled_at')
            ->update(['status' => EmailCampaignStatus::Scheduled, 'scheduled_at' => now()]);
        $stale->update(['status' => EmailCampaignStatus::Scheduled]);
    }
}
