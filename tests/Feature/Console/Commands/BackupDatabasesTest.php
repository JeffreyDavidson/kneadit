<?php

use App\Console\Commands\Operations\BackupDatabasesCommand;
use App\Models\Platform\Tenant;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    setUpCentralTest();
    config(['backups.path' => storage_path('framework/testing/backups')]);

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    File::ensureDirectoryExists(dirname($centralDatabase));
    createBackupSourceDatabase($centralDatabase, ['central note']);
    config(['database.connections.sqlite.database' => $centralDatabase]);
});

afterEach(function () {
    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    File::delete($centralDatabase);
    File::delete($centralDatabase.'-wal');
    File::delete($centralDatabase.'-shm');

    File::deleteDirectory(config('backups.path'));
});

/**
 * Creates a real SQLite database in WAL mode, as production runs.
 *
 * @param  array<int, string>  $notes
 */
function createBackupSourceDatabase(string $path, array $notes): void
{
    File::delete([$path, "{$path}-wal", "{$path}-shm"]);

    $pdo = new PDO("sqlite:{$path}");
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE notes (id INTEGER PRIMARY KEY, body TEXT)');

    foreach ($notes as $note) {
        $pdo->prepare('INSERT INTO notes (body) VALUES (?)')->execute([$note]);
    }
}

/** @return array<int, string> */
function readBackupNotes(string $path): array
{
    $pdo = new PDO("sqlite:{$path}");

    return $pdo->query('SELECT body FROM notes ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
}

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

test('backup writes a secured, standalone snapshot of the central database', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';

    try {
        $this->artisan('backup:databases')->assertSuccessful();

        expect("{$backupDirectory}/central.sqlite")
            ->toBeFile()
            ->and(fileperms("{$backupDirectory}/central.sqlite") & 0777)->toBe(0600)
            ->and(glob("{$backupDirectory}/*"))->toBe(["{$backupDirectory}/central.sqlite"])
            ->and(readBackupNotes("{$backupDirectory}/central.sqlite"))->toBe(['central note']);
    } finally {
        Carbon::setTestNow();
        File::deleteDirectory($backupDirectory);
    }
});

test('backup includes rows that are only in the write-ahead log and leaves out uncommitted ones', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';

    // A live app: the writer keeps the database open, so committed rows stay in
    // the -wal file (never checkpointed into the main file), and a second
    // transaction is still in flight when the backup runs.
    $writer = new PDO("sqlite:{$centralDatabase}");
    $writer->exec('PRAGMA wal_autocheckpoint = 0');
    $writer->exec("INSERT INTO notes (body) VALUES ('committed in wal')");

    $inFlight = new PDO("sqlite:{$centralDatabase}");
    $inFlight->exec('BEGIN IMMEDIATE');
    $inFlight->exec("INSERT INTO notes (body) VALUES ('not committed')");

    try {
        $this->artisan('backup:databases')->assertSuccessful();

        expect(glob("{$backupDirectory}/*"))->toBe(["{$backupDirectory}/central.sqlite"])
            ->and(readBackupNotes("{$backupDirectory}/central.sqlite"))->toBe(['central note', 'committed in wal']);
    } finally {
        $inFlight->exec('ROLLBACK');
        Carbon::setTestNow();
        File::deleteDirectory($backupDirectory);
    }
});

test('backup checks the integrity of every snapshot', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';

    $command = new #[Signature('backup:databases {--keep=7}')] class extends BackupDatabasesCommand
    {
        /** @var array<int, string> */
        public array $checked = [];

        protected function integrityCheck(string $path): string
        {
            $this->checked[] = basename($path);

            return parent::integrityCheck($path);
        }
    };
    app(Kernel::class)->registerCommand($command);

    try {
        $this->artisan('backup:databases')->assertSuccessful();

        expect($command->checked)->toBe(['central.sqlite']);
    } finally {
        Carbon::setTestNow();
        File::deleteDirectory($backupDirectory);
    }
});

test('backup fails loudly and publishes nothing when a snapshot fails its integrity check', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';

    app(Kernel::class)->registerCommand(new #[Signature('backup:databases {--keep=7}')] class extends BackupDatabasesCommand
    {
        protected function integrityCheck(string $path): string
        {
            return 'row 1 missing from index notes_body';
        }
    });

    try {
        $this->artisan('backup:databases')
            ->expectsOutputToContain('failed its integrity check: row 1 missing from index notes_body')
            ->assertFailed();

        expect($backupDirectory)->not->toBeDirectory()
            ->and(glob(dirname($backupDirectory).'/*.in-progress-*') ?: [])->toBeEmpty();
    } finally {
        Carbon::setTestNow();
        File::deleteDirectory($backupDirectory);
    }
});

test('backup fails when the central database is not a readable SQLite database', function () {
    Carbon::setTestNow('2026-08-25 15:00:00');

    $centralDatabase = storage_path('framework/testing/backup-central.sqlite');
    File::delete([$centralDatabase, "{$centralDatabase}-wal", "{$centralDatabase}-shm"]);
    File::put($centralDatabase, str_repeat('not a database', 200));
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';

    try {
        $this->artisan('backup:databases')
            ->expectsOutputToContain('Backup failed')
            ->assertFailed();

        expect($backupDirectory)->not->toBeDirectory();
    } finally {
        Carbon::setTestNow();
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

    $tenantDbDirectory = storage_path('framework/testing/backup-tenant-databases');
    $backupDirectory = config('backups.path').'/2026-08-25_15-00-00';
    File::ensureDirectoryExists($tenantDbDirectory);
    config(['tenancy.tenant_db_path' => $tenantDbDirectory]);

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
        createBackupSourceDatabase("{$tenantDbDirectory}/{$databaseName}", ['tenant note']);

        $this->artisan('backup:databases')
            ->expectsOutputToContain('1 tenant database(s)')
            ->assertSuccessful();

        expect("{$backupDirectory}/{$databaseName}")
            ->toBeFile()
            ->and(fileperms("{$backupDirectory}/{$databaseName}") & 0777)->toBe(0600)
            ->and(glob("{$backupDirectory}/{$databaseName}-*") ?: [])->toBeEmpty()
            ->and(readBackupNotes("{$backupDirectory}/{$databaseName}"))->toBe(['tenant note']);
    } finally {
        Carbon::setTestNow();
        File::deleteDirectory($tenantDbDirectory);
        File::deleteDirectory($backupDirectory);
    }
});
