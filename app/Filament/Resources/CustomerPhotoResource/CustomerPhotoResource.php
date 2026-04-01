<?php

namespace App\Filament\Resources\CustomerPhotoResource;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\CustomerPhotoResource\Pages\ListCustomerPhotos;
use App\Filament\Resources\CustomerPhotoResource\Schemas\CustomerPhotoForm;
use App\Filament\Resources\CustomerPhotoResource\Tables\CustomerPhotosTable;
use App\Models\Customers\CustomerPhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class CustomerPhotoResource extends Resource
{
    use ShowsUpgradeBadge;

    protected static ?string $model = CustomerPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Customer Photos';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return CustomerPhotoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerPhotosTable::configure($table);
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPhotos::route('/'),
        ];
    }
}
