<?php

namespace App\Filament\Resources\CapacityLimits;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\CapacityLimits\Schemas\CapacityLimitForm;
use App\Filament\Resources\CapacityLimits\Tables\CapacityLimitsTable;
use App\Models\CapacityLimit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class CapacityLimitResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = CapacityLimit::class;

    protected static ?string $navigationLabel = 'Capacity Limits';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedClock;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Settings';
    }

    public static function form(Schema $schema): Schema
    {
        return CapacityLimitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapacityLimitsTable::configure($table);
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
            'index' => Pages\ListCapacityLimits::route('/'),
        ];
    }
}
