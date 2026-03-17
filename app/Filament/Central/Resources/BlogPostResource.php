<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\BlogPostResource\Pages\CreateBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Pages\EditBlogPost;
use App\Filament\Central\Resources\BlogPostResource\Pages\ListBlogPosts;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Post Content')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Select::make('category')
                        ->options([
                            'guides' => 'Getting Started',
                            'laws' => 'Cottage Food Laws',
                            'tips' => 'Baker Tips',
                            'news' => 'KneadIt News',
                        ])
                        ->required(),
                    Textarea::make('excerpt')
                        ->rows(2)
                        ->maxLength(300)
                        ->columnSpanFull(),
                    RichEditor::make('body')
                        ->required()
                        ->columnSpanFull(),
                ]),
            Section::make('Publishing')
                ->schema([
                    Grid::make(2)->schema([
                        Toggle::make('is_published')
                            ->label('Published'),
                        DateTimePicker::make('published_at')
                            ->label('Publish Date'),
                    ]),
                    TextInput::make('featured_image')
                        ->label('Featured Image URL')
                        ->url()
                        ->placeholder('https://...'),
                ]),
            Section::make('SEO')
                ->schema([
                    TextInput::make('meta_title')
                        ->maxLength(70)
                        ->helperText('Leave blank to use post title'),
                    Textarea::make('meta_description')
                        ->maxLength(160)
                        ->rows(2),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('category')
                    ->badge()
                    ->colors([
                        'primary' => 'guides',
                        'success' => 'tips',
                        'warning' => 'laws',
                        'info' => 'news',
                    ]),
                IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                TextColumn::make('published_at')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'guides' => 'Getting Started',
                        'laws' => 'Cottage Food Laws',
                        'tips' => 'Baker Tips',
                        'news' => 'KneadIt News',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
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
