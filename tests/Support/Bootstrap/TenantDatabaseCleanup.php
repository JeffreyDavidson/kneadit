<?php

use Illuminate\Support\Facades\DB;

/**
 * Build the global tenant database cleanup hook used by feature and
 * integration tests. Browser fixture databases are intentionally preserved.
 */
function tenantDatabaseCleanup(): Closure
{
    $persistentTenantDbs = [
        'tenantbrowser-test',
        'tenantdemo',
    ];

    return function () use ($persistentTenantDbs): void {
        if (function_exists('tenancy') && tenancy()->initialized) {
            tenancy()->end();
        }

        DB::purge('tenant');

        gc_collect_cycles();
        foreach (glob(database_path('tenant*')) ?: [] as $file) {
            if (! is_file($file)) {
                continue;
            }

            if (in_array(basename($file), $persistentTenantDbs, true)) {
                continue;
            }

            @unlink($file);
            @unlink($file . '-journal');
            @unlink($file . '-wal');
            @unlink($file . '-shm');
        }
    };
}
