<?php

use App\Models\Platform\Tenant;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

beforeEach(function () {
    test()->directory = storage_path('framework/testing/sqlite-transaction-mode');
    File::deleteDirectory(test()->directory);
    File::ensureDirectoryExists(test()->directory);
    test()->databasePath = test()->directory.'/probe.sqlite';
    File::put(test()->databasePath, '');
});

afterEach(function () {
    File::deleteDirectory(test()->directory);
});

/**
 * Opens a connection to a real SQLite file using the options of a configured connection.
 */
function openProbeConnection(string $connectionName, string $databasePath): Connection
{
    return app('db.factory')->make([
        ...config("database.connections.{$connectionName}"),
        'database' => $databasePath,
        'url' => null,
    ], "probe-{$connectionName}");
}

test('the central and tenant template connections begin SQLite transactions as immediate', function (string $connectionName) {
    expect(config("database.connections.{$connectionName}.transaction_mode"))->toBe('IMMEDIATE');
})->with(['central', 'sqlite']);

test('a tenant database connection inherits the immediate transaction mode', function () {
    setUpCentralTest();
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['id' => 'transaction-mode-tenant']));

    expect($tenant->database()->connection())
        ->toHaveKey('transaction_mode', 'IMMEDIATE');
});

test('a read then write transaction waits for a concurrent writer instead of failing with database is locked', function (string $connectionName) {
    // Arrange
    $connection = openProbeConnection($connectionName, test()->databasePath);
    $connection->statement('create table probe (id integer primary key autoincrement, note text)');
    $connection->table('probe')->insert(['note' => 'seed']);

    $writer = new Process([
        PHP_BINARY,
        '-r',
        <<<'PHP'
        $pdo = new PDO('sqlite:'.$argv[1]);
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec("INSERT INTO probe (note) VALUES ('concurrent writer')");
        PHP,
        test()->databasePath,
    ]);

    // Act: read first (as checkout does), let another process try to write, then write.
    $connection->beginTransaction();
    $connection->table('probe')->count();

    $writer->start();
    usleep(500_000);

    $error = null;

    try {
        $connection->table('probe')->insert(['note' => 'transaction write']);
        $connection->commit();
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        $connection->rollBack();
    }

    $writer->wait();

    // Assert
    expect($error)->toBeNull()
        ->and($writer->isSuccessful())->toBeTrue($writer->getErrorOutput())
        ->and($connection->table('probe')->pluck('note')->all())
        ->toEqualCanonicalizing(['seed', 'transaction write', 'concurrent writer']);
})->with(['central', 'sqlite']);
