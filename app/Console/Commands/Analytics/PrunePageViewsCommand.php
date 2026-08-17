<?php

namespace App\Console\Commands\Analytics;

use App\Models\Engagement\PageView;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

#[Signature('analytics:prune-page-views {--days= : Delete page views older than this many days}')]
#[Description('Delete expired page-view analytics across all tenants')]
class PrunePageViewsCommand extends Command
{
    public function handle(TenancyManager $tenancy): int
    {
        $days = $this->retentionDays();

        if ($days === null) {
            return self::INVALID;
        }

        $cutoff = now()->subDays($days);
        $totalDeleted = 0;

        $failures = $tenancy->forEachTenant(function (Tenant $tenant) use ($cutoff, &$totalDeleted): void {
            $deleted = PageView::query()
                ->where('created_at', '<', $cutoff)
                ->delete();

            if (! is_int($deleted)) {
                throw new \UnexpectedValueException('Page-view deletion did not return a row count.');
            }

            $totalDeleted += $deleted;

            if ($deleted > 0) {
                $this->info("{$tenant->id}: pruned {$deleted}");
            }
        });

        $this->info("Total pruned: {$totalDeleted} (cutoff: {$cutoff->toIso8601String()})");

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function retentionDays(): ?int
    {
        $value = $this->option('days') ?? Config::integer('analytics.page_view_retention_days');
        $validator = Validator::make(['days' => $value], [
            'days' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first('days'));

            return null;
        }

        return (int) $value;
    }
}
