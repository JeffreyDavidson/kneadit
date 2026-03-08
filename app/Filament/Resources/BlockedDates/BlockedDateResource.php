<?php

namespace App\Filament\Resources\BlockedDates;

use App\Filament\Resources\BlockedDates\Pages\CreateBlockedDate;
use App\Filament\Resources\BlockedDates\Pages\EditBlockedDate;
use App\Filament\Resources\BlockedDates\Pages\ListBlockedDates;
use App\Filament\Resources\BlockedDates\Schemas\BlockedDateForm;
use App\Filament\Resources\BlockedDates\Tables\BlockedDatesTable;
use App\Models\BlockedDate;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use App\Traits\HasPlanGating;
class BlockedDateResource extends Resource
{
    use HasPlanGating;


    protected static string $requiredPlan = 'pro';
    protected static ?string $model = BlockedDate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return BlockedDateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlockedDatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlockedDates::route('/'),
            'create' => CreateBlockedDate::route('/create'),
            'edit' => EditBlockedDate::route('/{record}/edit'),
        ];
    }
}
