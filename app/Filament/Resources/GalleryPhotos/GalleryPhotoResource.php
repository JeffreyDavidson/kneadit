<?php

namespace App\Filament\Resources\GalleryPhotos;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Filament\Resources\GalleryPhotos\Schemas\GalleryPhotoForm;
use App\Filament\Resources\GalleryPhotos\Tables\GalleryPhotosTable;
use App\Models\Content\GalleryPhoto;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Pennant\Feature;

class GalleryPhotoResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = GalleryPhoto::class;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    #[\Override]
    protected static ?string $navigationLabel = 'Photo Gallery';

    #[\Override]
    protected static ?string $modelLabel = 'Gallery Photo';

    #[\Override]
    protected static ?string $pluralModelLabel = 'Gallery Photos';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return GalleryPhotoForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return GalleryPhotosTable::configure($table);
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
            'index' => ListGalleryPhotos::route('/'),
        ];
    }
}
