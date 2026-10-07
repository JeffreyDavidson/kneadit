<?php

use App\Services\Platform\HealthChecks\DatabaseBackupCheck;
use App\Services\Platform\ScheduledTaskMonitor;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    setUpCentralTest();
    Date::setTestNow('2026-10-06 16:00');

    test()->backupPath = storage_path('framework/testing/health-check-backups');
    File::deleteDirectory(test()->backupPath);
    File::ensureDirectoryExists(test()->backupPath);
    config(['backups.path' => test()->backupPath]);
});

afterEach(function () {
    File::deleteDirectory(test()->backupPath);
});

test('it passes when the newest backup is within 13 hours', function (string $backupName, string $reported) {
    File::ensureDirectoryExists(test()->backupPath."/{$backupName}");

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeTrue()
        ->and($result->message)->toBe("Database backup OK (newest {$reported})");
})->with([
    'an hour ago' => ['2026-10-06_15-00-00', '2026-10-06 15:00'],
    'just inside the limit' => ['2026-10-06_03-00-01', '2026-10-06 03:00'],
]);

test('it judges by the newest backup, not an older one', function () {
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-04_03-00-00');
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-06_15-00-00');

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeTrue();
});

test('it fails when the newest backup is older than 13 hours', function () {
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-06_02-59-59');
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-05_15-00-00');

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Database backup stale: the newest backup is from 2026-10-06 02:59');
});

test('it fails when there are no backups', function () {
    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Database backup missing: no backups found');
});

test('it fails when the backup directory does not exist', function () {
    File::deleteDirectory(test()->backupPath);

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Database backup missing: no backups found');
});

test('it ignores unfinished and unrelated entries', function () {
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-06_15-30-00.in-progress-abc123def456');
    File::put(test()->backupPath.'/2026-10-06_15-45-00', 'a file, not a backup');
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-05_03-00-00');

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Database backup stale: the newest backup is from 2026-10-05 03:00');
});

test('it fails when the last backup run failed even though an older backup is recent', function () {
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-06_15-00-00');
    $monitor = resolve(ScheduledTaskMonitor::class);
    $monitor->started('backup:databases');
    $monitor->succeeded('backup:databases', 4.2, 1);

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeFalse()
        ->and($result->message)->toBe('Database backup failed: the last scheduled run did not succeed (exit code 1)');
});

test('it passes again once a later run succeeds', function () {
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-06_15-00-00');
    $monitor = resolve(ScheduledTaskMonitor::class);
    $monitor->started('backup:databases');
    $monitor->succeeded('backup:databases', 4.2, 1);
    $monitor->started('backup:databases');
    $monitor->succeeded('backup:databases', 4.2, 0);

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeTrue();
});

test('it ignores a backup run that is still going', function () {
    File::ensureDirectoryExists(test()->backupPath.'/2026-10-06_15-00-00');
    resolve(ScheduledTaskMonitor::class)->started('backup:databases');

    $result = resolve(DatabaseBackupCheck::class)->run();

    expect($result->passed)->toBeTrue();
});
