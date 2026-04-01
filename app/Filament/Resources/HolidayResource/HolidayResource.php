<?php

namespace App\Filament\Resources\HolidayResource;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\HolidayResource\Schemas\HolidayForm;
use App\Filament\Resources\HolidayResource\Tables\HolidaysTable;
use App\Models\Operations\Holiday;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class HolidayResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = Holiday::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Holidays';

    protected static ?int $navigationSort = 9;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedSun;
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

    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
        ];
    }
}
