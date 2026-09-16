<?php

namespace App\Services\Engagement;

use App\Models\Platform\Tenant;
use App\Services\Engagement\Contracts\CustomerEngagement;
use App\Services\Engagement\Contracts\EngagementRecipient;
use App\Services\Notifications\ScheduledNotificationRunTracker;
use App\Services\Settings\TenantSettings;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EngagementDispatcher
{
    public function __construct(
        private readonly TenancyManager $tenancyManager,
        private readonly ScheduledNotificationRunTracker $runTracker,
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
                    $notificationKey = $this->notificationKey($engagement, $recipient);

                    if (! $this->runTracker->claim($notificationKey)) {
                        continue;
                    }

                    try {
                        $engagement->dispatchForRecipient($recipient, $settings);
                        $sent++;
                    } catch (\Throwable $e) {
                        $this->runTracker->release($notificationKey);
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

    private function notificationKey(CustomerEngagement $engagement, EngagementRecipient $recipient): string
    {
        $recipientId = $recipient->model->getKey();

        if (! is_int($recipientId) && ! is_string($recipientId)) {
            $recipientId = 'unsaved';
        }

        return sprintf(
            'engagement:%s:%s:%s:%s',
            $engagement::class,
            $recipient->model->getTable(),
            $recipientId,
            now()->toDateString(),
        );
    }
}
