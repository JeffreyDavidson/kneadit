<?php

namespace App\Filament\Resources\GalleryPhotos;

use App\Filament\Resources\GalleryPhotos\Pages\CreateGalleryPhoto;
use App\Filament\Resources\GalleryPhotos\Pages\EditGalleryPhoto;
use App\Filament\Resources\GalleryPhotos\Pages\ListGalleryPhotos;
use App\Filament\Traits\RequiresRole;
use App\Models\GalleryPhoto;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleryPhotoResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = GalleryPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Photo Gallery';

    protected static ?string $modelLabel = 'Gallery Photo';

    protected static ?string $pluralModelLabel = 'Gallery Photos';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
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
                                    ->options(function () {
                                        // Get categories from database or return predefined ones
                                        return [
                                            'products' => 'Products',
                                            'bakery' => 'Bakery Interior',
                                            'team' => 'Team',
                                            'events' => 'Events',
                                            'process' => 'Baking Process',
                                            'other' => 'Other',
                                        ];
                                    })
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
                            ->required()
                            ->directory('gallery')
                            ->imageEditor()
                            ->imagePreviewHeight('200')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Photo')
                    ->width(80)
                    ->height(60)
                    ->square(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),

                ToggleColumn::make('is_visible')
                    ->label('Visible'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Visibility')
                    ->boolean()
                    ->trueLabel('Visible only')
                    ->falseLabel('Hidden only')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->headerActions([
                CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryPhotos::route('/'),
            'create' => CreateGalleryPhoto::route('/create'),
            'edit' => EditGalleryPhoto::route('/{record}/edit'),
        ];
    }
}
