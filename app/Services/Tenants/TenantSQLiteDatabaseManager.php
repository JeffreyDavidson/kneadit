<?php

namespace App\Services\Tenants;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager;
use Throwable;

class TenantSQLiteDatabaseManager extends SQLiteDatabaseManager
{
    public function __construct(private readonly TenantDatabasePath $databasePath) {}

    protected function tenantDbPath(string $name): string
    {
        return $this->databasePath->resolve($name);
    }

    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        $databaseName = $tenant->database()->getName();

        if ($databaseName === null) {
            return false;
        }

        $path = $this->tenantDbPath($databaseName);
        $dir = dirname($path);

        File::ensureDirectoryExists($dir, 0700);

        if (File::exists($path) || is_link($path)) {
            return false;
        }

        $handle = fopen($path, 'x');

        if ($handle === false) {
            return false;
        }

        fclose($handle);

        if (File::chmod($path, 0600) !== true) {
            File::delete($path);

            throw new RuntimeException('Unable to secure the tenant database permissions.');
        }

        return true;
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        $databaseName = $tenant->database()->getName();

        if ($databaseName === null) {
            return false;
        }

        $path = $this->tenantDbPath($databaseName);

        // Audit logging so the next time tenant SQLite files vanish we know
        // who called for it. Captures the full call chain — Artisan command
        // (if any), running test class, and a stack trace. See investigation
        // notes around the orphan-tenant 503 (#474, #478).
        if (File::exists($path) || is_link($path)) {
            Log::warning('Tenant database deletion requested', [
                'tenant_id' => $tenant->getTenantKey(),
                'path' => $path,
                'artisan_command' => $this->currentArtisanCommand(),
                'running_test' => $this->currentTestClass(),
                'trace' => $this->compactTrace(),
            ]);
        }

        if (is_link($path)) {
            Log::error('Refusing to delete a symlink at a tenant database path.', [
                'tenant_id' => $tenant->getTenantKey(),
                'path' => $path,
            ]);

            return false;
        }

        if (File::exists($path)) {
            return File::delete($path);
        }

        return true;
    }

    private function currentArtisanCommand(): ?string
    {
        if (! app()->runningInConsole()) {
            return null;
        }

        $argv = $_SERVER['argv'] ?? [];

        return implode(' ', array_slice($argv, 1)) ?: null;
    }

    private function currentTestClass(): ?string
    {
        if (! defined('PHPUNIT_COMPOSER_INSTALL') && ! str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'pest')) {
            return null;
        }

        try {
            // Walk the stack looking for a Test class — gives us the actual
            // test that triggered the delete, even if it's wrapped in helpers.
            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
                $class = $frame['class'] ?? null;
                if ($class && str_contains($class, 'Tests\\')) {
                    return $class . '::' . $frame['function'];
                }
            }
        } catch (Throwable) {
            // best effort
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function compactTrace(): array
    {
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);

        // Drop the top two frames (this method + deleteDatabase itself);
        // keep the rest as a flat list of "Class::method (file:line)".
        return array_map(
            fn (array $f): string => sprintf(
                '%s%s%s (%s:%d)',
                $f['class'] ?? '',
                $f['type'] ?? (isset($f['class']) ? '::' : ''),
                $f['function'],
                isset($f['file']) ? str_replace(base_path() . '/', '', $f['file']) : '?',
                $f['line'] ?? 0,
            ),
            array_slice($frames, 2),
        );
    }

    public function databaseExists(string $name): bool
    {
        $path = $this->tenantDbPath($name);

        return ! is_link($path) && File::isFile($path);
    }

    /**
     * @param array<string, mixed> $baseConfig
     * @return array<string, mixed>
     */
    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $path = $this->tenantDbPath($databaseName);

        if (is_link($path)) {
            throw new RuntimeException('Refusing to connect through a tenant database symlink.');
        }

        if (File::isFile($path) && File::chmod($path, 0600) !== true) {
            throw new RuntimeException('Unable to secure the tenant database permissions.');
        }

        $baseConfig['database'] = $path;

        return $baseConfig;
    }
}
