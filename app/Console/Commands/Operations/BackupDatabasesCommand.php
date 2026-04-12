<?php

namespace App\Console\Commands\Operations;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

#[Signature('backup:databases {--keep=7 : Number of days to retain backups}')]
#[Description('Backup central and all tenant SQLite databases')]
class BackupDatabasesCommand extends Command
{
    public function handle(): int
    {
        $backupDir = $this->getBackupDir();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = "{$backupDir}/{$timestamp}";

        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $this->info("Backing up to: {$backupPath}");

        // 1. Backup central database
        $centralDb = config('database.connections.sqlite.database');
        if ($centralDb && file_exists($centralDb)) {
            $dest = "{$backupPath}/central.sqlite";
            copy($centralDb, $dest);
            $this->info('  ✓ Central DB (' . $this->formatSize((int) filesize($centralDb)) . ')');
        } else {
            $this->warn("  ⚠ Central DB not found at: {$centralDb}");
        }

        // 2. Backup all tenant databases
        $tenantDbDir = config('tenancy.tenant_db_path', database_path());
        if (is_dir($tenantDbDir)) {
            $tenantFiles = glob("{$tenantDbDir}/*.sqlite") ?: [];
            $count = 0;

            foreach ($tenantFiles as $tenantDb) {
                $filename = basename($tenantDb);
                copy($tenantDb, "{$backupPath}/{$filename}");
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
            mkdir($sharedDir, 0755, true);
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
        $files = glob("{$dir}/*") ?: [];
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($dir);
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
