<?php

use App\Services\Platform\HealthChecks\StorageLogsCheck;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    test()->storageRoot = storage_path('framework/testing/storage-logs-check-'.getmypid());

    File::deleteDirectory(test()->storageRoot);
    File::ensureDirectoryExists(test()->storageRoot);
    app()->useStoragePath(test()->storageRoot);
});

afterEach(function () {
    if (is_dir(test()->storageRoot.'/logs')) {
        chmod(test()->storageRoot.'/logs', 0755);
    }

    File::deleteDirectory(test()->storageRoot);
});

test('it passes when the logs directory is writable', function () {
    File::ensureDirectoryExists(test()->storageRoot.'/logs');

    $result = (new StorageLogsCheck)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe('Storage/logs writable');
});

test('it fails when the logs directory does not exist', function () {
    $result = (new StorageLogsCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Storage/logs directory does not exist');
});

test('it fails when the logs directory is not writable', function () {
    File::ensureDirectoryExists(test()->storageRoot.'/logs');
    chmod(test()->storageRoot.'/logs', 0555);

    $result = (new StorageLogsCheck)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Storage/logs directory not writable');
})->skip(fn (): bool => posix_geteuid() === 0, 'Root can write to any directory.');
