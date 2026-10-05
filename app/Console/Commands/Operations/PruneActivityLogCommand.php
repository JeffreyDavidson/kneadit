<?php

namespace App\Console\Commands\Operations;

use App\Models\Operations\ActivityLog;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('activity-log:prune {--days=365 : Delete activity older than this many days}')]
#[Description('Delete activity log rows older than the retention window across all tenants')]
class PruneActivityLogCommand extends Command
{
    public function handle(TenancyManager $tenancy): int
    {
        $validator = Validator::make(['days' => $this->option('days')], [
            'days' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('days'));

            return self::INVALID;
        }

        $cutoff = now()->subDays((int) $this->option('days'));
        $totalDeleted = 0;

        $failures = $tenancy->forEachTenant(function (Tenant $tenant) use ($cutoff, &$totalDeleted): void {
            $deleted = ActivityLog::query()
                ->where('created_at', '<', $cutoff)
                ->delete();

            if (! is_int($deleted)) {
                throw new \UnexpectedValueException('Activity log deletion did not return a row count.');
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
