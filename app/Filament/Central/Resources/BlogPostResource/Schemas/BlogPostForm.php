<?php

namespace App\Filament\Central\Resources\BlogPostResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Post Content')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique()
                        ->live(onBlur: true),
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
                        ->live(onBlur: true)
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
                ->description('Shown in Google search results and when shared on social.')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->schema([
                    View::make('filament.central.partials.blog-post-seo-preview'),
                    TextInput::make('meta_title')
                        ->maxLength(70)
                        ->live(debounce: 300)
                        ->helperText('Leave blank to use the post title.'),
                    Textarea::make('meta_description')
                        ->maxLength(160)
                        ->rows(2)
                        ->live(debounce: 300)
                        ->helperText('Leave blank to use the post excerpt.'),
                ]),
        ]);
    }
}
