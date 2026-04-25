<?php

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
    protected static ?string $model = ScheduledCheckin::class;

    protected static ?string $recordTitleAttribute = 'label';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Scheduled Check-ins';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return ScheduledCheckinForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return ScheduledCheckinsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScheduledCheckins::route('/'),
        ];
    }
}
