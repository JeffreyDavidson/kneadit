<?php

namespace App\Filament\Resources\SocialPosts;

use App\Enums\Marketing\SocialPostStatus;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Filament\Resources\SocialPosts\Schemas\SocialPostForm;
use App\Filament\Resources\SocialPosts\Tables\SocialPostsTable;
use App\Models\Content\SocialPost;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

class SocialPostResource extends Resource
{
    use ShowsUpgradeBadge;

    #[\Override]
    protected static ?string $model = SocialPost::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 7;

    #[\Override]
    protected static ?string $navigationLabel = 'Social Posts';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return SocialPostForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return SocialPostsTable::configure($table);
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
            'index' => ListSocialPosts::route('/'),
        ];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['caption'];
    }

    /** @param SocialPost $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return Str::limit($record->caption, 50);
    }

    /** @param SocialPost $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Platform' => $record->platform->getLabel(),
            'Status' => $record->status->getLabel(),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = cache()->remember('navigation-badge:social-posts:scheduled', 60, fn (): int => static::getModel()::query()->where('status', SocialPostStatus::Scheduled)->count());

        return $count > 0 ? (string) $count : null;
    }
}
