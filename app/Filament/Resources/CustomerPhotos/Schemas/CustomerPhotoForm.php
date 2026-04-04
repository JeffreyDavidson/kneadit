<?php

namespace App\Filament\Resources\CustomerPhotos\Schemas;

use App\Filament\Support\AllowedFileTypes;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            TextInput::make('customer_name')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('customer_email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),

                Section::make('Photo')
                    ->columnSpanFull()
                    ->components([
                        FileUpload::make('photo_path')
                            ->label('Photo')
                            ->image()
                            ->acceptedFileTypes(AllowedFileTypes::IMAGES)
                            ->maxSize(5120)
                            ->directory('customer-photos')
                            ->disk('public')
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),

                        Textarea::make('caption')
                            ->rows(3)
                            ->maxLength(1000),

                        Select::make('product_id')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->placeholder('No product linked'),
                    ]),

                Section::make('Status')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)->components([
                            Toggle::make('is_approved')
                                ->label('Approved'),
                            Toggle::make('is_featured')
                                ->label('Featured'),
                        ]),
                    ]),
            ]);
    }
}
