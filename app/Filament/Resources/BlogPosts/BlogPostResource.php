<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Resources\BlogPosts\Schemas\BlogPostForm;
use App\Filament\Resources\BlogPosts\Tables\BlogPostsTable;
use App\Models\Content\TenantBlogPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class BlogPostResource extends Resource
{
    #[\Override]
    protected static ?string $model = TenantBlogPost::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Content';

    #[\Override]
    protected static ?int $navigationSort = 1;

    #[\Override]
    protected static ?string $navigationLabel = 'Blog Posts';

    #[\Override]
    protected static ?string $modelLabel = 'Blog Post';

    #[\Override]
    protected static ?string $pluralModelLabel = 'Blog Posts';

    #[\Override]
    public static function form(Schema $schema): Schema
    {
        return BlogPostForm::configure($schema);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return BlogPostsTable::configure($table);
    }

    #[\Override]
    public static function getRelations(): array
    {
        return [];
    }

    #[\Override]
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'excerpt'];
    }

    /** @param TenantBlogPost $record */
    #[\Override]
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    /** @param TenantBlogPost $record */
    #[\Override]
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Author' => $record->author_name ?? 'N/A',
            'Published' => $record->is_published ? ($record->published_at?->format('M d, Y') ?? 'Yes') : 'Draft',
        ];
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
