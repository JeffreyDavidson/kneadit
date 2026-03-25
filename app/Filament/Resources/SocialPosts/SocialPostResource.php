<?php

namespace App\Filament\Resources\SocialPosts;

use App\Enums\SocialPostStatus;
use App\Enums\UserRole;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Filament\Resources\SocialPosts\Schemas\SocialPostForm;
use App\Filament\Resources\SocialPosts\Tables\SocialPostsTable;
use App\Filament\Traits\RequiresRole;
use App\Models\SocialPost;
use App\Traits\HasPlanGating;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SocialPostResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): UserRole
    {
        return UserRole::Manager;
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = SocialPost::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-share';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Social Posts';

    public static function form(Schema $schema): Schema
    {
        return SocialPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SocialPostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSocialPosts::route('/'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['caption'];
    }

    /** @param SocialPost $record */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return Str::limit($record->caption, 50);
    }

    /** @param SocialPost $record */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Platform' => ucfirst($record->platform ?? 'N/A'),
            'Status' => ucfirst($record->status ?? 'draft'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::query()->where('status', SocialPostStatus::Scheduled)->count() ?: null;
    }
}
