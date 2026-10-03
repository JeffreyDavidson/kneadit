<?php

/**
 * Paratest numbers each worker process through the TEST_TOKEN environment
 * variable. It is absent in a serial run, so nothing below changes a serial run.
 */
function parallelTestToken(): ?string
{
    $token = getenv('TEST_TOKEN');

    return $token === false || $token === ''
        ? null
        : $token;
}

/**
 * Directory the tenant SQLite files are created in. Each worker gets its own,
 * because tenant ids such as "demo" are fixed and the cleanup hook deletes every
 * tenant file it finds, which would pull files out from under another worker.
 */
function testTenantDatabaseDirectory(): string
{
    $token = parallelTestToken();

    return $token === null
        ? database_path()
        : storage_path("framework/testing/tenant-databases-{$token}");
}

function testTenantDatabaseFile(string $name): string
{
    return testTenantDatabaseDirectory().DIRECTORY_SEPARATOR.$name;
}

/**
 * Give each worker its own temporary directory. Tests that count or delete
 * files in the system temp directory would otherwise see other workers' files.
 * PHP caches the directory on the first sys_get_temp_dir() call, so this has to
 * run before anything else asks for it.
 */
function isolateParallelTemporaryDirectory(): void
{
    $token = parallelTestToken();

    if ($token === null) {
        return;
    }

    $directory = dirname(__DIR__, 3)."/storage/framework/testing/tmp-{$token}";

    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    putenv("TMPDIR={$directory}");
}
