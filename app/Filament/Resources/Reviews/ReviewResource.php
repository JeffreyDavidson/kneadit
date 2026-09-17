<?php

namespace App\Filament\Resources\Reviews;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Engagement\Review;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class ReviewResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = Review::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'customer_name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    #[\Override]
    public static function canAccess(): bool
    {
        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_name', 'customer_email'];
    }

    /** @param Review $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Review by '.$record->customer_name;
    }

    /** @param Review $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Rating' => str_repeat('⭐', $record->rating ?? 0),
            'Product' => $record->product->name ?? 'N/A',
        ];
    }

    #[\Override]
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('product');
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
        ];
    }
}
