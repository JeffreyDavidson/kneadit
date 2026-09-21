<?php

namespace App\Filament\Resources\Holidays;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Holidays\Schemas\HolidayForm;
use App\Filament\Resources\Holidays\Tables\HolidaysTable;
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

    #[\Override]
    protected static ?string $model = Holiday::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static ?string $navigationLabel = 'Holidays';

    #[\Override]
    protected static ?int $navigationSort = 9;

    #[\Override]
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedSun;
    }

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return 'Tools';
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return HolidayForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return HolidaysTable::configure($table);
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHolidays::route('/'),
        ];
    }
}
