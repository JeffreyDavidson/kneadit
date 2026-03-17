<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product Details')
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

                        Textarea::make('description')
                            ->rows(3),

                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::query()->pluck('name', 'id'))
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Pricing')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextInput::make('price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01),

                                TextInput::make('cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->helperText('Used for margin calculation'),

                                TextInput::make('margin')
                                    ->label('Margin %')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($state, ?Model $record) {
                                        if ($record && $record->cost && $record->price) {
                                            return round(($record->price - $record->cost) / $record->price * 100, 2).'%';
                                        }

                                        return null;
                                    }),
                            ]),
                    ]),

                Section::make('Settings')
                    ->components([
                        Grid::make(2)
                            ->components([
                                Toggle::make('is_active')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->default(false),
                            ]),

                        FileUpload::make('image')
                            ->image()
                            ->directory('products')
                            ->visibility('public'),
                    ]),
            ]);
    }
}
