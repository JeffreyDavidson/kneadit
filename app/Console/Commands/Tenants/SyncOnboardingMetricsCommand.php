<?php

namespace App\Console\Commands\Tenants;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantOnboardingMetrics;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('tenants:sync-onboarding-metrics')]
#[Description('Refresh centrally stored onboarding counts from each tenant database')]
class SyncOnboardingMetricsCommand extends Command
{
    public function handle(TenantOnboardingMetrics $metrics): int
    {
        $synced = 0;
        $failed = 0;

        foreach (Tenant::query()->cursor() as $tenant) {
            try {
                $metrics->sync($tenant);
                $synced++;
            } catch (Throwable $exception) {
                $failed++;

                Log::warning('Tenant onboarding metrics sync failed.', [
                    'tenant' => $tenant->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $this->info("Onboarding metrics synced: {$synced}; failed: {$failed}.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
