<?php

use App\Services\Platform\HealthChecks\DatabaseConnectionCheck;

test('it passes when the database connection opens', function () {
    $result = (new DatabaseConnectionCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Database connection OK');
});

test('it fails with the connection error when the database is unreachable', function () {
    config([
        'database.connections.unreachable' => [
            'driver' => 'sqlite',
            'database' => storage_path('framework/testing/missing-directory/missing.sqlite'),
        ],
        'database.default' => 'unreachable',
    ]);

    $result = (new DatabaseConnectionCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toStartWith('Database connection failed: ');
});
