<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\CreateReview;
use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Review;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use App\Filament\Traits\RequiresRole;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static ?string $model = Review::class;
    protected static string $requiredPlan = 'growth';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';
    protected static ?int $navigationSort = 1;
    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['customer_name', 'customer_email'];
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return 'Review by ' . $record->customer_name;
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Rating' => str_repeat('⭐', $record->rating ?? 0),
            'Product' => $record->product?->name ?? 'N/A',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('product');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'create' => CreateReview::route('/create'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
