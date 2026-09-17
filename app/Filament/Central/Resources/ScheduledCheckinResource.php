<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\ScheduledCheckinResource\Pages;
use App\Filament\Central\Resources\ScheduledCheckinResource\Schemas\ScheduledCheckinForm;
use App\Filament\Central\Resources\ScheduledCheckinResource\Tables\ScheduledCheckinsTable;
use App\Models\Operations\ScheduledCheckin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ScheduledCheckinResource extends Resource
{
    #[\Override]
    protected static ?string $model = ScheduledCheckin::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?string $navigationLabel = 'Scheduled Check-ins';

    #[\Override]
    protected static ?int $navigationSort = 3;

    #[\Override]
    public static function form(Schema $form): Schema
    {
        return ScheduledCheckinForm::configure($form);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ScheduledCheckinsTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduledCheckins::route('/'),
        ];
    }
}
