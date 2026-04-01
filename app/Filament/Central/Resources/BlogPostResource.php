<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\BlogPostResource\Pages\CreateBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Filament\Central\Resources\BlogPostResource\Schemas\BlogPostForm;
use App\Filament\Central\Resources\BlogPostResource\Tables\BlogPostsTable;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return BlogPostForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return BlogPostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
