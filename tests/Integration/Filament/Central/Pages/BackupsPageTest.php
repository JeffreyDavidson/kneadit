<?php

use App\Filament\Central\Pages\Backups;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('page renders', function () {
    livewire(Backups::class)->assertOk();
});

test('isSafeBackupName accepts valid timestamps and rejects path traversal', function () {
    expect(Backups::isSafeBackupName('2026-04-25_12-34-56'))->toBeTrue()
        ->and(Backups::isSafeBackupName('../etc/passwd'))->toBeFalse()
        ->and(Backups::isSafeBackupName('/'))->toBeFalse()
        ->and(Backups::isSafeBackupName('not-a-timestamp'))->toBeFalse()
        ->and(Backups::isSafeBackupName(''))->toBeFalse();
});

test('formatBytes scales correctly', function () {
    expect(Backups::formatBytes(500))->toBe('500 B')
        ->and(Backups::formatBytes(2048))->toBe('2.0 KB')
        ->and(Backups::formatBytes(5 * 1024 * 1024))->toBe('5.0 MB');
});

test('parseTimestamp returns null for unsafe names', function () {
    expect(Backups::parseTimestamp('../etc'))->toBeNull()
        ->and(Backups::parseTimestamp('2026-04-25_12-34-56'))->not->toBeNull();
});

test('getBackups lists completed backups and skips in-progress staging folders', function () {
    config(['backups.path' => storage_path('framework/testing/backups-page')]);
    File::deleteDirectory(config('backups.path'));
    File::ensureDirectoryExists(config('backups.path').'/2026-10-05_03-00-00');
    File::put(config('backups.path').'/2026-10-05_03-00-00/central.sqlite', 'x');
    File::ensureDirectoryExists(config('backups.path').'/2026-10-06_15-30-00.in-progress-abc123def456');

    $names = collect(livewire(Backups::class)->instance()->getBackups())->pluck('name')->all();

    File::deleteDirectory(config('backups.path'));

    expect($names)->toBe(['2026-10-05_03-00-00']);
});

test('an in-progress staging folder is not a safe backup name for delete or download', function () {
    expect(Backups::isSafeBackupName('2026-10-06_15-30-00.in-progress-abc123def456'))->toBeFalse();
});
