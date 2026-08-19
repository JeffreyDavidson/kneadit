<?php

namespace App\Console\Commands\Customers;

use App\Actions\Marketing\SendCustomerCampaign;
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
                $due = CustomerCampaign::query()->due()->get();

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
