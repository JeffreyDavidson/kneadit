<?php

use App\Filament\Central\Pages\Backups;
use App\Models\Staff\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    setUpCentralTest();
    actingAs(User::factory()->platformAdmin()->create());
    Filament::setCurrentPanel(Filament::getPanel('central'));
});

test('page renders', function () {
    Livewire::test(Backups::class)->assertOk();
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
