<?php

namespace App\Console\Commands\Operations;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantDatabasePath;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

#[Signature('backup:databases {--keep=7 : Number of days to retain backups}')]
#[Description('Backup central and all tenant SQLite databases')]
class BackupDatabasesCommand extends Command
{
    public function handle(TenantDatabasePath $tenantDatabasePath): int
    {
        $backupDir = $this->getBackupDir();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = "{$backupDir}/{$timestamp}";

        File::ensureDirectoryExists($backupPath, 0755);

        $this->info("Backing up to: {$backupPath}");

        // 1. Backup central database
        $centralDb = Config::string('database.connections.sqlite.database');
        if ($centralDb !== '' && file_exists($centralDb)) {
            $dest = "{$backupPath}/central.sqlite";
            $this->copyDatabase($centralDb, $dest);
            $this->info('  ✓ Central DB (' . $this->formatSize((int) filesize($centralDb)) . ')');
        } else {
            $this->warn("  ⚠ Central DB not found at: {$centralDb}");
        }

        // 2. Backup all tenant databases
        $tenantDbDir = Config::string('tenancy.tenant_db_path', database_path());
        if (is_dir($tenantDbDir)) {
            $count = 0;

            foreach (Tenant::all() as $tenant) {
                $databaseName = (string) $tenant->database()->getName();
                $tenantDb = $tenantDatabasePath->resolve($databaseName);

                if (! is_file($tenantDb) || is_link($tenantDb)) {
                    $this->warn("  ⚠ Tenant DB not found: {$databaseName}");

                    continue;
                }

                $filename = basename($tenantDb);
                $destination = "{$backupPath}/{$filename}";
                $this->copyDatabase($tenantDb, $destination);
                $count++;
            }

            $this->info("  ✓ {$count} tenant database(s)");
        } else {
            $this->info('  - No tenant DB directory found');
        }

        // 3. Clean old backups
        $keep = (int) $this->option('keep');
        $this->cleanOldBackups($backupDir, $keep);

        $totalSize = $this->dirSize($backupPath);
        $this->info("Backup complete ({$this->formatSize($totalSize)})");

        Log::info('Database backup completed', [
            'path' => $backupPath,
            'size' => $totalSize,
        ]);

        return Command::SUCCESS;
    }

    protected function getBackupDir(): string
    {
        // Use a shared directory outside releases for Forge deploys
        $sharedDir = dirname(base_path()) . '/backups';

        // Fallback for local dev
        if (Str::contains(base_path(), '/current/') || Str::contains(base_path(), '/releases/')) {
            // Forge zero-downtime deploy — go up to project root
            $projectRoot = preg_replace('#/(current|releases/\d+)$#', '', base_path());
            $sharedDir = "{$projectRoot}/backups";
        }

        if (! is_dir($sharedDir)) {
            File::ensureDirectoryExists($sharedDir, 0755);
        }

        return $sharedDir;
    }

    protected function cleanOldBackups(string $backupDir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays)->timestamp;
        $dirs = glob("{$backupDir}/20*", GLOB_ONLYDIR) ?: [];
        $removed = 0;

        foreach ($dirs as $dir) {
            if (filemtime($dir) < $cutoff) {
                $this->removeDir($dir);
                $removed++;
            }
        }

        if ($removed > 0) {
            $this->info("  🗑 Cleaned {$removed} old backup(s) (>{$keepDays} days)");
        }
    }

    protected function removeDir(string $dir): void
    {
        throw_unless(
            File::deleteDirectory($dir),
            RuntimeException::class,
            "Failed to remove old backup directory {$dir}.",
        );
    }

    protected function copyDatabase(string $source, string $destination): void
    {
        throw_unless(
            File::copy($source, $destination),
            RuntimeException::class,
            "Failed to copy database backup to {$destination}.",
        );

        throw_unless(
            File::chmod($destination, 0600),
            RuntimeException::class,
            "Failed to secure database backup at {$destination}.",
        );

        $sidecars = Arr::reject(
            ['-wal', '-shm'],
            fn (string $suffix): bool => is_link($source . $suffix) || ! File::isFile($source . $suffix),
        );

        foreach ($sidecars as $suffix) {
            $sidecarSource = $source . $suffix;

            $sidecarDestination = $destination . $suffix;
            throw_unless(
                File::copy($sidecarSource, $sidecarDestination),
                RuntimeException::class,
                "Failed to copy database sidecar backup to {$sidecarDestination}.",
            );

            throw_unless(
                File::chmod($sidecarDestination, 0600),
                RuntimeException::class,
                "Failed to secure database sidecar backup at {$sidecarDestination}.",
            );
        }
    }

    protected function dirSize(string $dir): int
    {
        $size = 0;
        foreach (glob("{$dir}/*") ?: [] as $file) {
            $size += filesize($file);
        }

        return $size;
    }

    protected function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return round($bytes / 1024, 1) . ' KB';
    }
}
