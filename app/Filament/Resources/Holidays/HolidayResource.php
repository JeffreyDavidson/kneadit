<?php

namespace App\Filament\Resources\Holidays;

use App\Enums\UserRole;
use App\Filament\Resources\Holidays\Schemas\HolidayForm;
use App\Filament\Resources\Holidays\Tables\HolidaysTable;
use App\Filament\Traits\RequiresRole;
use App\Models\Holiday;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = Holiday::class;

    protected static ?string $navigationLabel = 'Holidays';

    protected static ?int $navigationSort = 9;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-sun';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Tools';
    }

    public static function form(Schema $schema): Schema
    {
        return HolidayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HolidaysTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
        ];
    }
}
