<?php

namespace Tests\Feature;

use Tests\CentralTestCase;

class BackupDatabasesTest extends CentralTestCase
{
    public function test_backup_command_exists(): void
    {
        $this->artisan('backup:databases')
            ->assertSuccessful();
    }

    public function test_backup_command_accepts_keep_option(): void
    {
        $this->artisan('backup:databases', ['--keep' => 3])
            ->assertSuccessful();
    }

    public function test_backup_command_class_has_correct_signature(): void
    {
        $command = new \App\Console\Commands\BackupDatabases();
        $this->assertStringContainsString('backup:databases', $command->getName());
    }

    public function test_backup_creates_backup_directory(): void
    {
        $this->artisan('backup:databases');

        // Check that either the shared backup dir or a fallback exists
        $possibleDirs = [
            dirname(base_path()) . '/backups',
            base_path() . '/../backups',
        ];

        $found = false;
        foreach ($possibleDirs as $dir) {
            if (is_dir($dir)) {
                $found = true;
                // Clean up
                $subdirs = glob("{$dir}/20*", GLOB_ONLYDIR);
                foreach ($subdirs as $subdir) {
                    array_map('unlink', glob("{$subdir}/*"));
                    rmdir($subdir);
                }
                break;
            }
        }

        $this->assertTrue($found, 'Backup directory should be created');
    }
}
