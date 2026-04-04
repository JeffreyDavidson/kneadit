<?php

namespace App\Services\Engagement;

use App\Contracts\Engagement\CustomerEngagement;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EngagementDispatcher
{
    public function __construct(
        private TenancyManager $tenancyManager,
    ) {}

    /**
     * Run an engagement type across all tenants.
     * Returns the number of failed tenants.
     */
    public function dispatch(CustomerEngagement $engagement, Command $output): int
    {
        return $this->tenancyManager->forEachTenant(
            function (Tenant $tenant, TenantSettings $settings) use ($engagement, $output) {
                if (! $engagement->isEnabled($settings)) {
                    $output->info("Skipping {$tenant->id} — disabled");

                    return;
                }

                $recipients = $engagement->findRecipients($settings);

                if ($recipients->isEmpty()) {
                    return;
                }

                $sent = 0;

                foreach ($recipients as $recipient) {
                    try {
                        $engagement->dispatchForRecipient($recipient, $settings);
                        $sent++;
                    } catch (\Throwable $e) {
                        $output->error("Failed for {$recipient->name}: {$e->getMessage()}");
                        $engagementClass = $engagement::class;
                        Log::warning("{$engagementClass} failed", [
                            'tenant' => $tenant->id,
                            'recipient' => $recipient->name,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                if ($sent > 0) {
                    $output->info("[{$tenant->id}] Processed {$sent} recipient(s).");
                }
            },
            function (Tenant $tenant, \Throwable $e) use ($engagement, $output) {
                $output->error("Tenant {$tenant->id} failed: {$e->getMessage()}");
                $engagementClass = $engagement::class;
                Log::warning("{$engagementClass} tenant failed", [
                    'tenant' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
            },
        );
    }
}
