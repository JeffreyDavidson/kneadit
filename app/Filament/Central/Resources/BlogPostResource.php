<?php

declare(strict_types=1);

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\BlogPostResource\Pages\CreateBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Filament\Central\Resources\BlogPostResource\Schemas\BlogPostForm;
use App\Filament\Central\Resources\BlogPostResource\Tables\BlogPostsTable;
use App\Models\Content\BlogPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    #[\Override]
    protected static ?string $model = BlogPost::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'title';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    #[\Override]
    protected static ?int $navigationSort = 20;

    #[\Override]
    public static function form(Schema $form): Schema
    {
        return BlogPostForm::configure($form);
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return BlogPostsTable::configure($table);
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
