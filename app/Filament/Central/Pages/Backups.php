<?php

namespace App\Filament\Central\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;
use UnitEnum;

class Backups extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Backups';

    protected static ?string $title = 'Database Backups';

    protected static ?int $navigationSort = 60;

    protected string $view = 'filament.central.pages.backups';

    /**
     * Resolve the directory the BackupDatabasesCommand writes into. Same
     * algorithm as the command — shared dir outside Forge release folders
     * so backups survive deploys.
     */
    public static function backupDirectory(): string
    {
        if (Str::contains(base_path(), '/current/') || Str::contains(base_path(), '/releases/')) {
            $projectRoot = preg_replace('#/(current|releases/\d+)$#', '', base_path());

            return "{$projectRoot}/backups";
        }

        return dirname(base_path()) . '/backups';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBackups(): array
    {
        $dir = self::backupDirectory();

        if (! is_dir($dir)) {
            return [];
        }

        $folders = File::directories($dir);

        $backups = [];
        foreach ($folders as $folder) {
            if (! is_string($folder)) {
                continue;
            }

            $name = basename($folder);
            $files = File::files($folder);
            $tenantFiles = collect($files)->filter(fn (\Symfony\Component\Finder\SplFileInfo $file): bool => $file->getFilename() !== 'central.sqlite');

            $backups[] = [
                'name' => $name,
                'created_at' => self::parseTimestamp($name),
                'size' => collect($files)->sum(fn (\Symfony\Component\Finder\SplFileInfo $file): int => $file->getSize()),
                'central' => collect($files)->contains(fn (\Symfony\Component\Finder\SplFileInfo $file): bool => $file->getFilename() === 'central.sqlite'),
                'tenant_count' => $tenantFiles->count(),
            ];
        }

        // Newest first.
        usort($backups, fn (array $a, array $b): int => strcmp($b['name'], $a['name']));

        return $backups;
    }

    public function runBackup(): void
    {
        try {
            $exit = Artisan::call('backup:databases');
            $output = trim(Artisan::output());

            if ($exit === 0) {
                Notification::make()
                    ->title('Backup complete')
                    ->body($output !== '' ? $output : 'All databases archived.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title("Backup returned exit code {$exit}")
                    ->body($output)
                    ->danger()
                    ->send();
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title('Backup failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteBackup(string $name): void
    {
        if (! self::isSafeBackupName($name)) {
            Notification::make()->title('Invalid backup name')->danger()->send();

            return;
        }

        $path = self::backupDirectory() . '/' . $name;

        if (! is_dir($path)) {
            Notification::make()->title('Backup not found')->danger()->send();

            return;
        }

        File::deleteDirectory($path);

        Notification::make()
            ->title('Backup deleted')
            ->body("Removed {$name}.")
            ->success()
            ->send();
    }

    /**
     * Backup folder names match the command's timestamp format Y-m-d_H-i-s.
     * Reject anything else to prevent path traversal in delete/download.
     */
    public static function isSafeBackupName(string $name): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}$/', $name) === 1;
    }

    public static function parseTimestamp(string $name): ?Carbon
    {
        if (! self::isSafeBackupName($name)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d_H-i-s', $name);
        } catch (Throwable) {
            return null;
        }
    }

    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }
        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / 1024 / 1024, 1) . ' MB';
        }

        return number_format($bytes / 1024 / 1024 / 1024, 2) . ' GB';
    }
}
