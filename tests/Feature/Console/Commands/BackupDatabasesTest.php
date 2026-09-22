<?php

use App\Console\Commands\Operations\BackupDatabasesCommand;
use App\Models\Platform\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    setUpCentralTest();
    config(['backups.path' => storage_path('framework/testing/backups')]);

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    File::ensureDirectoryExists(dirname($centralDatabase));
    File::put($centralDatabase, 'central database');
    config(['database.connections.sqlite.database' => $centralDatabase]);
});

afterEach(function () {
    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    File::delete($centralDatabase);
    File::delete($centralDatabase.'-wal');
    File::delete($centralDatabase.'-shm');

    File::deleteDirectory(config('backups.path'));
});

test('backup command exists', function () {
    $this->artisan('backup:databases')
        ->assertSuccessful();
});

test('backup command accepts keep option', function () {
    $this->artisan('backup:databases', ['--keep' => 3])
        ->assertSuccessful();
});

test('backup command class has correct signature', function () {
    $command = new BackupDatabasesCommand;

    expect($command->getName())->toContain('backup:databases');
});

test('backup creates backup directory', function () {
    $this->artisan('backup:databases');

    expect(config('backups.path'))->toBeDirectory();
});

test('backup outputs progress messages', function () {
    $this->artisan('backup:databases')
        ->expectsOutputToContain('Backing up to')
        ->expectsOutputToContain('Backup complete')
        ->assertSuccessful();
});

test('backup logs completion', function () {
    Log::shouldReceive('info')
        ->once()
        ->withArgs(fn ($message, $context) => str_contains($message, 'Database backup completed')
            && isset($context['path'])
            && isset($context['size']));

    $this->artisan('backup:databases')
        ->assertSuccessful();
});

test('backup default keep is 7 days', function () {
    $command = new BackupDatabasesCommand;
    $definition = $command->getDefinition();
    $keepOption = $definition->getOption('keep');

    expect($keepOption->getDefault())->toBe('7');
});

test('backup creates timestamped subdirectory', function () {
    $this->artisan('backup:databases');

    $subdirs = glob(config('backups.path').'/20*', GLOB_ONLYDIR) ?: [];

    expect($subdirs)->not->toBeEmpty()
        ->and(basename($subdirs[0]))->toMatch('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}$/');
});

test('backup secures the central database copy', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';
    File::put($centralDatabase, 'central database');
    File::put($centralDatabase.'-wal', 'central wal');
    File::put($centralDatabase.'-shm', 'central shm');
    config(['database.connections.sqlite.database' => $centralDatabase]);

    try {
        $this->artisan('backup:databases')->assertSuccessful();

        expect("{$backupDirectory}/central.sqlite")
            ->toBeFile()
            ->and(fileperms("{$backupDirectory}/central.sqlite") & 0777)->toBe(0600)
            ->and("{$backupDirectory}/central.sqlite-wal")->toBeFile()
            ->and("{$backupDirectory}/central.sqlite-shm")->toBeFile();
    } finally {
        Carbon::setTestNow();
        File::delete($centralDatabase);
        File::delete($centralDatabase.'-wal');
        File::delete($centralDatabase.'-shm');
        File::deleteDirectory($backupDirectory);
    }
});

test('backup fails when the central database is missing', function () {
    config(['database.connections.sqlite.database' => '/nonexistent/path/db.sqlite']);

    $this->artisan('backup:databases')
        ->expectsOutputToContain('Central DB not found')
        ->expectsOutputToContain('Backup incomplete')
        ->assertFailed();
});

test('backup fails when a tenant database is missing', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    $tenantDbDirectory = storage_path('framework/testing/backup-tenant-databases');
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';
    File::put($centralDatabase, 'central database');
    File::ensureDirectoryExists($tenantDbDirectory);
    config([
        'database.connections.sqlite.database' => $centralDatabase,
        'tenancy.tenant_db_path' => $tenantDbDirectory,
    ]);

    try {
        Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'missing-backup-tenant']));

        $this->artisan('backup:databases')
            ->expectsOutputToContain('Tenant DB not found')
            ->expectsOutputToContain('Backup incomplete')
            ->assertFailed();

        expect($backupDirectory)->not->toBeDirectory()
            ->and(glob(dirname($backupDirectory).'/*.in-progress-*') ?: [])->toBeEmpty();
    } finally {
        Carbon::setTestNow();
        File::delete($centralDatabase);
        File::deleteDirectory($tenantDbDirectory);
        File::deleteDirectory($backupDirectory);
    }
});

test('backup reports tenant database count or missing directory', function () {
    $this->artisan('backup:databases')
        ->assertSuccessful();
});

test('backup includes extensionless tenant databases from the configured directory', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $tenantDbDirectory = storage_path('framework/testing/backup-tenant-databases');
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';
    File::ensureDirectoryExists($tenantDbDirectory);
    config(['tenancy.tenant_db_path' => $tenantDbDirectory]);

    try {
        $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'backup-tenant']));
        $databaseName = (string) $tenant->database()->getName();
        File::put("{$tenantDbDirectory}/{$databaseName}", 'tenant database');
        File::put("{$tenantDbDirectory}/{$databaseName}-wal", 'tenant wal');
        File::put("{$tenantDbDirectory}/{$databaseName}-shm", 'tenant shm');

        $this->artisan('backup:databases')
            ->expectsOutputToContain('1 tenant database(s)')
            ->assertSuccessful();

        expect("{$backupDirectory}/{$databaseName}")
            ->toBeFile()
            ->and(fileperms("{$backupDirectory}/{$databaseName}") & 0777)->toBe(0600)
            ->and("{$backupDirectory}/{$databaseName}-wal")->toBeFile()
            ->and("{$backupDirectory}/{$databaseName}-shm")->toBeFile();
    } finally {
        Carbon::setTestNow();
        File::deleteDirectory($tenantDbDirectory);
        File::deleteDirectory($backupDirectory);
    }
});
