<?php

use App\Models\Platform\Tenant;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\File;

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
function openProbeConnection(string $connectionName, string $databasePath, int $busyTimeout): Connection
{
    return resolve('db.factory')->make([
        ...config("database.connections.{$connectionName}"),
        'database' => $databasePath,
        'busy_timeout' => $busyTimeout,
        'url' => null,
    ], "probe-{$connectionName}-{$busyTimeout}");
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

test('a read then write transaction keeps its write lock so a concurrent writer cannot invalidate its read', function (string $connectionName) {
    // Arrange: a transaction (as checkout runs) and a second writer that gives up at once instead of waiting.
    $transaction = openProbeConnection($connectionName, test()->databasePath, 5000);
    $transaction->statement('create table probe (id integer primary key autoincrement, note text)');
    $transaction->table('probe')->insert(['note' => 'seed']);
    $otherWriter = openProbeConnection($connectionName, test()->databasePath, 0);

    // Act: read first, let the other writer try to commit in between, then write.
    $transaction->beginTransaction();
    $transaction->table('probe')->count();

    $otherWriterError = null;

    try {
        $otherWriter->table('probe')->insert(['note' => 'other writer']);
    } catch (Throwable $exception) {
        $otherWriterError = $exception->getMessage();
    }

    $transactionError = null;

    try {
        $transaction->table('probe')->insert(['note' => 'transaction write']);
        $transaction->commit();
    } catch (Throwable $exception) {
        $transactionError = $exception->getMessage();
        $transaction->rollBack();
    }

    $otherWriter->table('probe')->insert(['note' => 'other writer retry']);

    // Assert: the other writer was held off (it would have waited for busy_timeout), so the transaction was not
    // failed with "database is locked", and the other writer succeeds once the transaction commits.
    expect($otherWriterError)->toContain('database is locked')
        ->and($transactionError)->toBeNull()
        ->and($transaction->table('probe')->pluck('note')->all())
        ->toBe(['seed', 'transaction write', 'other writer retry']);
})->with(['central', 'sqlite']);
