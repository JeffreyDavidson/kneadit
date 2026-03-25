<?php

namespace App\Filament\Resources\GalleryPhotos\Schemas;

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
            ->schema([
                Section::make('Photo Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('category')
                                    ->options([
                                        'products' => 'Products',
                                        'bakery' => 'Bakery Interior',
                                        'team' => 'Team',
                                        'events' => 'Events',
                                        'process' => 'Baking Process',
                                        'other' => 'Other',
                                    ])
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
