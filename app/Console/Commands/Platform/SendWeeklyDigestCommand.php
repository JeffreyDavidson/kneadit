<?php

namespace App\Console\Commands\Platform;

use App\Enums\Staff\UserRole;
use App\Events\Platform\WeeklyDigestRequested;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Services\Reporting\WeeklyDigestDataCollector;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWeeklyDigestCommand extends Command
{
    protected $signature = 'digest:weekly';

    protected $description = 'Send weekly digest email to bakery owners';

    public function handle(TenancyManager $tenancyManager): int
    {
        $tenants = Tenant::query()->cursor();
        $failures = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () use ($tenant) {
                    if (settings('weekly_digest_enabled', '1') !== '1') {
                        $this->info("Skipping {$tenant->id} — digest disabled");

                        return;
                    }

                    $users = User::query()->where('role', UserRole::Owner)->get();

                    if ($users->isEmpty()) {
                        $users = User::query()->limit(1)->get();
                    }

                    $data = resolve(WeeklyDigestDataCollector::class)->collect();

                    foreach ($users as $user) {
                        WeeklyDigestRequested::dispatch(
                            $user,
                            $data['stats'],
                            $data['topProducts'],
                            $data['atRiskCustomers'],
                            $data['upcomingCount'],
                            $data['storeName'],
                            $data['adminUrl'],
                        );
                    }

                    $this->info("Sent digest for {$tenant->id} to {$users->count()} user(s)");
                });
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->id}: {$e->getMessage()}");
                Log::warning('Weekly digest processing failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $failures++;
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
