<?php

namespace App\Filament\Resources\Suppliers;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\RelationManagers\IngredientsRelationManager;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Inventory\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;
use UnitEnum;

class SupplierResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Supplier::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Shop';

    #[\Override]
    protected static ?int $navigationSort = 9;

    #[\Override]
    protected static ?string $navigationLabel = 'Suppliers';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
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
    public static function getRelations(): array
    {
        return [
            IngredientsRelationManager::class,
        ];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'contact_name', 'email'];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
        ];
    }
}
