<?php

namespace App\Filament\Resources\SocialPosts;

use App\Filament\Resources\SocialPosts\Pages\CreateSocialPost;
use App\Filament\Resources\SocialPosts\Pages\EditSocialPost;
use App\Filament\Resources\SocialPosts\Pages\ListSocialPosts;
use App\Filament\Resources\SocialPosts\Schemas\SocialPostForm;
use App\Filament\Resources\SocialPosts\Tables\SocialPostsTable;
use App\Models\SocialPost;
use Filament\Resources\Resource;
use App\Filament\Traits\RequiresRole;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

use App\Traits\HasPlanGating;
class SocialPostResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
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
            'create' => CreateSocialPost::route('/create'),
            'edit' => EditSocialPost::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'scheduled')->count() ?: null;
    }
}
