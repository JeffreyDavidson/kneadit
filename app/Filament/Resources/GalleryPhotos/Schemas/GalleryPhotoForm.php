<?php

namespace App\Filament\Resources\GalleryPhotos\Schemas;

use App\Enums\GalleryCategory;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Photo Information')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('category')
                                    ->options(GalleryCategory::class)
                                    ->placeholder('Select a category')
                                    ->searchable(),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),

                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),
                            ]),

                        FileUpload::make('image_path')
                            ->label('Photo')
                            ->image()
                            ->maxSize(5120)
                            ->required()
                            ->disk('public')
                            ->directory('gallery')
                            ->imageEditor()
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
