<?php

namespace App\Console\Commands\Operations;

use App\Models\Operations\WebhookDelivery;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('webhooks:prune {--days=30 : Delete deliveries older than this many days}')]
#[Description('Delete webhook_deliveries rows older than the configured retention window across all tenants')]
class PruneWebhookDeliveriesCommand extends Command
{
    public function handle(TenancyManager $tenancy): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);
        $totalDeleted = 0;

        $failures = $tenancy->forEachTenant(function (Tenant $tenant) use ($cutoff, &$totalDeleted): void {
            $deleted = WebhookDelivery::where('dispatched_at', '<', $cutoff)->delete();

            if (! is_int($deleted)) {
                return;
            }

            $totalDeleted += $deleted;

            if ($deleted > 0) {
                $this->info("{$tenant->id}: pruned {$deleted}");
            }
        });

        $this->info("Total pruned: {$totalDeleted} (cutoff: {$cutoff->toIso8601String()})");

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
