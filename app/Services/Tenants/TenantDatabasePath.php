<?php

namespace App\Services\Tenants;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;

class TenantDatabasePath
{
    public function resolve(string $databaseName): string
    {
        if (preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/D', $databaseName) !== 1) {
            throw new InvalidArgumentException('Tenant database name contains unsafe path characters.');
        }

        $root = rtrim(Config::string('tenancy.tenant_db_path', database_path()), '/\\');

        if ($root === '') {
            throw new InvalidArgumentException('Tenant database path is not configured.');
        }

        return $root . DIRECTORY_SEPARATOR . $databaseName;
    }
}
