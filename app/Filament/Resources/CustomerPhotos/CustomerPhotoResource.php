<?php

namespace App\Filament\Resources\CustomerPhotos;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos;
use App\Filament\Resources\CustomerPhotos\Schemas\CustomerPhotoForm;
use App\Filament\Resources\CustomerPhotos\Tables\CustomerPhotosTable;
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

    #[\Override]
    protected static ?string $model = CustomerPhoto::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?string $navigationLabel = 'Customer Photos';

    #[\Override]
    protected static ?int $navigationSort = 9;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return CustomerPhotoForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return CustomerPhotosTable::configure($table);
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
        return [];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPhotos::route('/'),
        ];
    }
}
