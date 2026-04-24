<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable;
use App\Models\Operations\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only viewer for the ActivityLog timeline. Every model that uses
 * LogsActivityObserver writes a row on create/update/delete; this resource
 * surfaces the same data in a filterable, paginated admin table.
 *
 * Mutations are deliberately disabled — the audit trail is not editable.
 */
class ActivityLogResource extends Resource
{
    use RequiresManagerRole;

    protected static ?string $model = ActivityLog::class;

    protected static ?string $recordTitleAttribute = 'description';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Activity Log';

    protected static ?int $navigationSort = 99;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return ActivityLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
