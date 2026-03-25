<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Details')
                    ->columnSpanFull()
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, callable $set) {
                                $set('slug', Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->required(),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Images')
                    ->columnSpanFull()
                    ->description('First image becomes the primary/default image. Drag to reorder.')
                    ->components([
                        Repeater::make('productImages')
                            ->relationship('images')
                            ->label('')
                            ->reorderable(true)
                            ->reorderableWithDragAndDrop(true)
                            ->reorderableWithButtons(true)
                            ->orderColumn('sort_order')
                            ->defaultItems(0)
                            ->addActionLabel('Add Image')
                            ->columns(1)
                            ->itemLabel(fn (array $state): string => ! empty($state['path']) ? 'Product Image' : 'New Image')
                            ->components([
                                FileUpload::make('path')
                                    ->label('Image')
                                    ->image()
                                    ->maxSize(5120)
                                    ->directory('products')
                                    ->visibility('public')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Settings')
                    ->columnSpanFull()
                    ->components([
                        Grid::make(2)
                            ->components([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}
