<?php

namespace App\Filament\Resources\CapacityLimits;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\CapacityLimits\Schemas\CapacityLimitForm;
use App\Filament\Resources\CapacityLimits\Tables\CapacityLimitsTable;
use App\Models\Operations\CapacityLimit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class CapacityLimitResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = CapacityLimit::class;

    #[\Override]
    protected static ?string $navigationLabel = 'Capacity Limits';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedClock;
    }

    #[\Override]
    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return CapacityLimitForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return CapacityLimitsTable::configure($table);
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
            'index' => Pages\ListCapacityLimits::route('/'),
        ];
    }
}
