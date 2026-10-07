<?php

namespace App\Console\Commands\Operations;

use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantDatabasePath;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

#[Signature('backup:databases {--keep=7 : Number of days to retain backups}')]
#[Description('Backup central and all tenant SQLite databases')]
class BackupDatabasesCommand extends Command
{
    public function handle(TenantDatabasePath $tenantDatabasePath): int
    {
        $backupDir = $this->getBackupDir();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = "{$backupDir}/{$timestamp}";
        $stagingPath = "{$backupPath}.in-progress-".Str::random(12);

        File::ensureDirectoryExists($stagingPath, 0755);

        $this->info("Backing up to: {$backupPath}");
        $backupComplete = true;

        try {
            // 1. Backup central database
            $centralDb = Config::string('database.connections.sqlite.database');
            if ($centralDb !== '' && file_exists($centralDb)) {
                $dest = "{$stagingPath}/central.sqlite";
                $this->snapshotDatabase($centralDb, $dest);
                $this->info('  ✓ Central DB ('.$this->formatSize((int) filesize($centralDb)).')');
            } else {
                $this->warn("  ⚠ Central DB not found at: {$centralDb}");
                $backupComplete = false;
            }

            // 2. Backup all tenant databases
            $tenants = Tenant::all();
            $tenantDbDir = Config::string('tenancy.tenant_db_path', database_path());
            if (is_dir($tenantDbDir)) {
                $count = 0;

                foreach ($tenants as $tenant) {
                    $databaseName = (string) $tenant->database()->getName();
                    $tenantDb = $tenantDatabasePath->resolve($databaseName);

                    if (! is_file($tenantDb) || is_link($tenantDb)) {
                        $this->warn("  ⚠ Tenant DB not found: {$databaseName}");
                        $backupComplete = false;

                        continue;
                    }

                    $filename = basename($tenantDb);
                    $destination = "{$stagingPath}/{$filename}";
                    $this->snapshotDatabase($tenantDb, $destination);
                    $count++;
                }

                $this->info("  ✓ {$count} tenant database(s)");
            } elseif ($tenants->isNotEmpty()) {
                $this->warn("  ⚠ Tenant DB directory not found at: {$tenantDbDir}");
                $backupComplete = false;
            } else {
                $this->info('  - No tenant DB directory found');
            }

            $totalSize = $this->dirSize($stagingPath);

            if (! $backupComplete) {
                $this->error("Backup incomplete ({$this->formatSize($totalSize)})");

                Log::error('Database backup incomplete', [
                    'path' => $backupPath,
                    'size' => $totalSize,
                ]);

                return Command::FAILURE;
            }

            if (! rename($stagingPath, $backupPath)) {
                throw new RuntimeException("Failed to publish database backup at {$backupPath}.");
            }

            // 3. Clean old backups
            $keep = (int) $this->option('keep');
            $this->cleanOldBackups($backupDir, $keep);

            $this->info("Backup complete ({$this->formatSize($totalSize)})");

            Log::info('Database backup completed', [
                'path' => $backupPath,
                'size' => $totalSize,
            ]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Backup failed: {$e->getMessage()}");

            Log::error('Database backup failed', [
                'path' => $backupPath,
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        } finally {
            if (is_dir($stagingPath)) {
                File::deleteDirectory($stagingPath);
            }
        }
    }

    protected function getBackupDir(): string
    {
        $sharedDir = Config::string('backups.path');

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

    /**
     * Writes a consistent, standalone snapshot of a live SQLite database. VACUUM INTO
     * reads one transaction's view of the database (including rows that are still
     * only in the -wal file) and writes a single file, so a checkpoint or write
     * while the backup runs cannot produce a torn copy. The copy is then checked
     * with PRAGMA integrity_check; anything but "ok" fails the backup.
     */
    protected function snapshotDatabase(string $source, string $destination): void
    {
        $previousUmask = umask(0077);

        $connection = $this->openSqliteConnection($source, busyTimeoutMilliseconds: 30000);

        try {
            $connection->statement('VACUUM INTO ?', [$destination]);
        } finally {
            $connection->disconnect();
            umask($previousUmask);
        }

        throw_unless(
            File::chmod($destination, 0600),
            RuntimeException::class,
            "Failed to secure database backup at {$destination}.",
        );

        $result = $this->integrityCheck($destination);

        throw_unless(
            $result === 'ok',
            RuntimeException::class,
            "Database backup {$destination} failed its integrity check: {$result}",
        );
    }

    /** Returns "ok", or the first problem SQLite reports for the database file. */
    protected function integrityCheck(string $path): string
    {
        $connection = $this->openSqliteConnection($path, busyTimeoutMilliseconds: 5000);

        try {
            $result = $connection->scalar('PRAGMA integrity_check');
        } finally {
            $connection->disconnect();
        }

        return is_string($result) ? $result : 'no result';
    }

    /**
     * A throwaway connection to one SQLite file, built from the app's SQLite
     * settings but leaving the file's journal mode alone (a backup copy must stay
     * a single file). It is not registered with the database manager, so it
     * never becomes a configured connection.
     */
    private function openSqliteConnection(string $path, int $busyTimeoutMilliseconds): Connection
    {
        return resolve('db.factory')->make([
            ...Config::array('database.connections.sqlite'),
            'url' => null,
            'database' => $path,
            'busy_timeout' => $busyTimeoutMilliseconds,
            'journal_mode' => null,
            'synchronous' => null,
        ], 'backup-snapshot');
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
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1024, 1).' KB';
    }
}
