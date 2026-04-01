<?php

namespace App\Console\Commands;

use App\Events\Platform\HealthCheckFailed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthCheckCommand extends Command
{
    protected $signature = 'health:check';

    protected $description = 'Run platform health checks and alert on failures';

    public function handle(): int
    {
        $issues = [];

        // 1. Database connectivity
        try {
            DB::connection()->getPdo();
            $this->info('✓ Database connection OK');
        } catch (\Exception $e) {
            $issues[] = "Database connection failed: {$e->getMessage()}";
            $this->error('✗ Database connection FAILED');
        }

        // 2. Central DB has tables
        try {
            $userCount = DB::table('users')->count();
            $this->info("✓ Users table OK ({$userCount} users)");
        } catch (\Exception $e) {
            $issues[] = "Users table query failed: {$e->getMessage()}";
            $this->error('✗ Users table FAILED');
        }

        // 3. Tenant DB directory writable
        $tenantDbDir = config('tenancy.tenant_db_path', database_path());
        if (is_dir($tenantDbDir) && is_writable($tenantDbDir)) {
            $tenantDbs = count(glob("{$tenantDbDir}/*.sqlite") ?: []);
            $this->info("✓ Tenant DB directory writable ({$tenantDbs} databases)");
        } else {
            $issues[] = "Tenant DB directory not writable: {$tenantDbDir}";
            $this->error('✗ Tenant DB directory NOT writable');
        }

        // 4. Disk space
        $freeBytes = disk_free_space(base_path());
        $freeGb = round($freeBytes / 1073741824, 1);
        if ($freeGb < 1) {
            $issues[] = "Low disk space: {$freeGb} GB free";
            $this->error("✗ Low disk space: {$freeGb} GB");
        } else {
            $this->info("✓ Disk space OK ({$freeGb} GB free)");
        }

        // 5. Storage directory writable
        $logsPath = storage_path('logs');
        if (is_dir($logsPath) && is_writable($logsPath)) {
            $this->info('✓ Storage/logs writable');
        } elseif (! is_dir($logsPath)) {
            $issues[] = 'Storage/logs directory does not exist';
            $this->error('✗ Storage/logs directory MISSING');
        } else {
            $issues[] = 'Storage/logs directory not writable';
            $this->error('✗ Storage/logs NOT writable');
        }

        // 6. Homepage responds
        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(2, 100)->get(config('app.url'));
            if ($response->successful()) {
                $this->info('✓ Homepage responds (' . $response->status() . ')');
            } else {
                $issues[] = "Homepage returned status {$response->status()}";
                $this->error("✗ Homepage returned {$response->status()}");
            }
        } catch (\Exception $e) {
            $issues[] = "Homepage unreachable: {$e->getMessage()}";
            $this->error('✗ Homepage unreachable');
        }

        // Alert if issues found
        if (! empty($issues)) {
            $this->alertOnIssues($issues);

            return Command::FAILURE;
        }

        $this->info("\nAll health checks passed.");

        return Command::SUCCESS;
    }

    /** @param array<int, string> $issues */
    protected function alertOnIssues(array $issues): void
    {
        $issueText = implode("\n- ", $issues);
        $message = "KneadIt Health Check Alert\n\nIssues detected:\n- {$issueText}\n\nTime: " . now()->toDateTimeString();

        Log::critical('Health check failed', ['issues' => $issues]);

        HealthCheckFailed::dispatch($message);
        $this->info('Health check alert dispatched.');
    }
}
