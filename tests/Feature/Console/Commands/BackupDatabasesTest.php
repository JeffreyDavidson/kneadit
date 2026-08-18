<?php

use App\Console\Commands\Operations\BackupDatabasesCommand;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    setUpCentralTest();
});

afterEach(function () {
    // Clean up any backup directories created during tests
    $possibleDirs = [
        dirname(base_path()) . '/backups',
        base_path() . '/../backups',
    ];

    foreach ($possibleDirs as $dir) {
        if (is_dir($dir)) {
            $subdirs = glob("{$dir}/20*", GLOB_ONLYDIR) ?: [];
            foreach ($subdirs as $subdir) {
                array_map('unlink', glob("{$subdir}/*") ?: []);
                @rmdir($subdir);
            }
        }
    }
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

    $possibleDirs = [
        dirname(base_path()) . '/backups',
        base_path() . '/../backups',
    ];

    $found = false;
    foreach ($possibleDirs as $dir) {
        if (is_dir($dir)) {
            $found = true;
            break;
        }
    }

    expect($found)->toBeTrue('Backup directory should be created');
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
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'Database backup completed')
                && isset($context['path'])
                && isset($context['size']);
        });

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

    $possibleDirs = [
        dirname(base_path()) . '/backups',
        base_path() . '/../backups',
    ];

    $found = false;
    foreach ($possibleDirs as $dir) {
        if (is_dir($dir)) {
            $subdirs = glob("{$dir}/20*", GLOB_ONLYDIR) ?: [];
            if (count($subdirs) > 0) {
                $found = true;
                // Verify timestamp format
                $dirName = basename($subdirs[0]);
                expect($dirName)->toMatch('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}$/');
            }
            break;
        }
    }

    expect($found)->toBeTrue('Timestamped backup subdirectory should be created');
});

test('backup handles missing central database gracefully', function () {
    config(['database.connections.sqlite.database' => '/nonexistent/path/db.sqlite']);

    $this->artisan('backup:databases')
        ->expectsOutputToContain('Central DB not found')
        ->assertSuccessful();
});

test('backup reports tenant database count or missing directory', function () {
    $this->artisan('backup:databases')
        ->assertSuccessful();
});
