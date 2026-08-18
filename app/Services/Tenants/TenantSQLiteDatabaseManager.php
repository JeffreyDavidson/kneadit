<?php

namespace App\Services\Tenants;

use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager;
use Throwable;

class TenantSQLiteDatabaseManager extends SQLiteDatabaseManager
{
    protected function tenantDbPath(string $name): string
    {
        $sharedBase = config('tenancy.tenant_db_path');

        if ($sharedBase) {
            return $sharedBase . '/' . $name;
        }

        return database_path($name);
    }

    public function createDatabase(mixed $tenant): bool
    {
        $path = $this->tenantDbPath($tenant->database()->getName());
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return (bool) file_put_contents($path, '');
    }

    public function deleteDatabase(mixed $tenant): bool
    {
        $path = $this->tenantDbPath($tenant->database()->getName());

        // Audit logging so the next time tenant SQLite files vanish we know
        // who called for it. Captures the full call chain — Artisan command
        // (if any), running test class, and a stack trace. See investigation
        // notes around the orphan-tenant 503 (#474, #478).
        if (file_exists($path)) {
            Log::warning('Tenant database deletion requested', [
                'tenant_id' => $tenant->id ?? null,
                'path' => $path,
                'artisan_command' => $this->currentArtisanCommand(),
                'running_test' => $this->currentTestClass(),
                'trace' => $this->compactTrace(),
            ]);
        }

        if (file_exists($path)) {
            return unlink($path);
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
        return file_exists($this->tenantDbPath($name));
    }

    /** @return array<string, mixed> */
    /**
     * @param array<string, mixed> $baseConfig
     * @return array<string, mixed>
     */
    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $baseConfig['database'] = $this->tenantDbPath($databaseName);

        return $baseConfig;
    }
}
