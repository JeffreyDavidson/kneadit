<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Filament\Support\AllowedFileTypes;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->components([
                        self::contentSection(),
                    ]),

                Group::make()
                    ->columnSpan(1)
                    ->components([
                        self::publishingSection(),
                        self::featuredImageSection(),
                        self::tagsSection(),
                    ]),
            ]);
    }

    private static function contentSection(): Section
    {
        return Section::make('Post')
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(),

                Textarea::make('excerpt')
                    ->rows(3)
                    ->maxLength(500),

                RichEditor::make('body')
                    ->required()
                    ->extraInputAttributes(['style' => 'min-height: 30rem']),
            ]);
    }

    private static function publishingSection(): Section
    {
        return Section::make('Publishing')
            ->components([
                Toggle::make('is_published')
                    ->label('Published'),

                DateTimePicker::make('published_at')
                    ->label('Publish Date'),
            ]);
    }

    private static function featuredImageSection(): Section
    {
        return Section::make('Featured Image')
            ->components([
                FileUpload::make('featured_image')
                    ->hiddenLabel()
                    ->image()
                    ->acceptedFileTypes(AllowedFileTypes::IMAGES)
                    ->maxSize(5120)
                    ->directory('blog-images')
                    ->disk('public')
                    ->preventFilePathTampering(),
            ]);
    }

    private static function tagsSection(): Section
    {
        return Section::make('Tags')
            ->components([
                TagsInput::make('tags')
                    ->hiddenLabel(),
            ]);
    }
}
