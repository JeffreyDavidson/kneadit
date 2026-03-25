<?php

namespace App;

use Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager;

class TenantSQLiteDatabaseManager extends SQLiteDatabaseManager
{
    protected function tenantDbPath(string $name): string
    {
        $sharedBase = config('tenancy.tenant_db_path');

        if ($sharedBase) {
            return $sharedBase.'/'.$name;
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

        if (file_exists($path)) {
            return unlink($path);
        }

        return true;
    }

    public function databaseExists(string $name): bool
    {
        return file_exists($this->tenantDbPath($name));
    }

    /** @return array<string, mixed> */
    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $baseConfig['database'] = $this->tenantDbPath($databaseName);

        return $baseConfig;
    }
}
