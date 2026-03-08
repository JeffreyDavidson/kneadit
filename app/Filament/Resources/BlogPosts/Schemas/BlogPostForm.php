<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

            TextInput::make('slug')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Textarea::make('excerpt')
                ->rows(3)
                ->maxLength(500),

            RichEditor::make('body')
                ->required()
                ->columnSpanFull(),

            FileUpload::make('featured_image')
                ->image()
                ->directory('blog-images')
                ->disk('public'),

            TagsInput::make('tags'),

            TextInput::make('author_name')
                ->maxLength(255),

            Toggle::make('is_published')
                ->label('Published')
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if ($state && !$get('published_at')) {
                        $set('published_at', now()->format('Y-m-d H:i:s'));
                    }
                }),

            DateTimePicker::make('published_at')
                ->label('Publish Date'),
        ]);
    }
}
