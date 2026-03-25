<?php

namespace App\Filament\Resources\Suppliers;

use App\Enums\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Suppliers\Pages\ListSuppliers;
use App\Filament\Resources\Suppliers\RelationManagers\IngredientsRelationManager;
use App\Filament\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\Resources\Suppliers\Tables\SuppliersTable;
use App\Models\Supplier;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class SupplierResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = Supplier::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'Suppliers';

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    public static function getRelations(): array
    {
        return [
            IngredientsRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'contact_name', 'email'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
        ];
    }
}
