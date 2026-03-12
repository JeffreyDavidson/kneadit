<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HealthCheck extends Command
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
            $tenantDbs = count(glob("{$tenantDbDir}/*.sqlite"));
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
        if (is_writable(storage_path('logs'))) {
            $this->info('✓ Storage/logs writable');
        } else {
            $issues[] = 'Storage/logs directory not writable';
            $this->error('✗ Storage/logs NOT writable');
        }

        // 6. Homepage responds
        try {
            $response = Http::timeout(10)->get('https://getkneadit.app');
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

    protected function alertOnIssues(array $issues): void
    {
        $issueText = implode("\n- ", $issues);
        $message = "KneadIt Health Check Alert\n\nIssues detected:\n- {$issueText}\n\nTime: " . now()->toDateTimeString();

        Log::critical('Health check failed', ['issues' => $issues]);

        // Send email alert
        try {
            $alertEmail = config('mail.platform_notify', 'jeffrey@getkneadit.app');
            Mail::raw($message, function ($m) use ($alertEmail) {
                $m->to($alertEmail)
                    ->subject('⚠️ KneadIt Health Check Alert')
                    ->from(config('mail.from.address'), 'KneadIt Platform');
            });
            $this->info('Alert email sent to ' . $alertEmail);
        } catch (\Exception $e) {
            Log::error('Failed to send health check alert email', ['error' => $e->getMessage()]);
            $this->error('Failed to send alert email: ' . $e->getMessage());
        }
    }
}
