<?php

namespace App\Filament\Resources\CustomerPhotos;

use App\Filament\Resources\CustomerPhotos\Pages\CreateCustomerPhoto;
use App\Filament\Resources\CustomerPhotos\Pages\EditCustomerPhoto;
use App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos;
use App\Filament\Traits\RequiresRole;
use App\Models\CustomerPhoto;
use App\Traits\HasPlanGating;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CustomerPhotoResource extends Resource
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static ?string $model = CustomerPhoto::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?string $navigationLabel = 'Customer Photos';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->disk('public')
                    ->width(80)
                    ->height(60),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('caption')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('—')
                    ->toggleable(),

                ToggleColumn::make('is_approved')
                    ->label('Approved'),

                ToggleColumn::make('is_featured')
                    ->label('Featured'),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('Approval')
                    ->boolean()
                    ->trueLabel('Approved')
                    ->falseLabel('Pending')
                    ->native(false),

                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueLabel('Featured')
                    ->falseLabel('Not featured')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Collection $records) => $records->each->update(['is_approved' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerPhotos::route('/'),
            'create' => CreateCustomerPhoto::route('/create'),
            'edit' => EditCustomerPhoto::route('/{record}/edit'),
        ];
    }
}
