<?php

namespace App\Console\Commands\Customers;

use App\Actions\Marketing\SendCustomerCampaign;
use App\Enums\Marketing\CustomerCampaignStatus;
use App\Models\Engagement\CustomerCampaign;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('campaigns:send-scheduled')]
#[Description('Send any customer campaigns whose scheduled_at has arrived')]
class SendScheduledCampaignsCommand extends Command
{
    public function handle(TenancyManager $tenancyManager): int
    {
        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings): void {
                // A paused bakery sends nothing to its customers; its campaigns stay
                // scheduled and go out on the next run after it resumes.
                if ($tenant->is_paused) {
                    return;
                }

                // A send that died partway is put back to Scheduled and resumes;
                // the sender skips recipients that already have a log row. A "Send
                // now" campaign has no scheduled_at, so give it one or it is never due.
                $stale = CustomerCampaign::query()
                    ->where('status', CustomerCampaignStatus::Sending)
                    ->where('updated_at', '<=', now()->subHour());

                $stale->clone()
                    ->whereNull('scheduled_at')
                    ->update(['status' => CustomerCampaignStatus::Scheduled, 'scheduled_at' => now()]);
                $stale->update(['status' => CustomerCampaignStatus::Scheduled]);

                $due = CustomerCampaign::query()
                    ->where('status', CustomerCampaignStatus::Scheduled)
                    ->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', now())
                    ->get();

                if ($due->isEmpty()) {
                    return;
                }

                $sender = resolve(SendCustomerCampaign::class);
                foreach ($due as $campaign) {
                    $sent = $sender($campaign);
                    $this->info("{$tenant->id}: campaign #{$campaign->id} sent to {$sent} recipient(s)");
                }
            },
        );

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
