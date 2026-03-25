<?php

namespace App\Filament\Resources\CustomerPhotos\Schemas;

use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)->schema([
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
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Photo')
                            ->image()
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
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_approved')
                                ->label('Approved'),
                            Toggle::make('is_featured')
                                ->label('Featured'),
                        ]),
                    ]),
            ]);
    }
}
