<?php

use App\Console\Commands\BackupDatabases;

beforeEach(function () {
    setUpCentralTest();
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
    $command = new BackupDatabases;

    expect($command->getName())->toContain('backup:databases');
});

test('backup creates backup directory', function () {
    $this->artisan('backup:databases');

    $possibleDirs = [
        dirname(base_path()).'/backups',
        base_path().'/../backups',
    ];

    $found = false;
    foreach ($possibleDirs as $dir) {
        if (is_dir($dir)) {
            $found = true;
            $subdirs = glob("{$dir}/20*", GLOB_ONLYDIR);
            foreach ($subdirs as $subdir) {
                array_map('unlink', glob("{$subdir}/*"));
                rmdir($subdir);
            }
            break;
        }
    }

    expect($found)->toBeTrue('Backup directory should be created');
});
