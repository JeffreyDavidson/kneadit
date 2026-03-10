<?php

namespace App\Filament\Central\Resources;

use App\Filament\Central\Resources\AnnouncementResource\Pages;
use App\Models\PlatformAnnouncement;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class AnnouncementResource extends Resource
{
    protected static ?string $model = PlatformAnnouncement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Announcements';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Announcement Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('body')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->options([
                                'info' => 'Info',
                                'warning' => 'Warning',
                                'success' => 'Success',
                                'maintenance' => 'Maintenance',
                            ])
                            ->required()
                            ->default('info'),
                        CheckboxList::make('target_plans')
                            ->label('Target Plans')
                            ->options([
                                'all' => 'All Plans',
                                'starter' => 'Starter',
                                'growth' => 'Growth',
                                'pro' => 'Pro',
                            ])
                            ->required()
                            ->rule('min:1')
                            ->helperText('Select which plans should see this announcement'),
                    ]),
                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Toggle::make('is_dismissable')
                            ->label('Dismissable')
                            ->default(true),
                        DateTimePicker::make('starts_at')
                            ->label('Starts At'),
                        DateTimePicker::make('ends_at')
                            ->label('Ends At'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'info' => 'info',
                        'warning' => 'warning',
                        'success' => 'success',
                        'maintenance' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('target_plans')
                    ->label('Target')
                    ->formatStateUsing(fn ($state) => empty($state) ? 'All Plans' : (is_array($state) ? implode(', ', $state) : 'All Plans'))
                    ->badge(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Actions\EditAction::make()
                    ->slideOver(),
            ])
            ->bulkActions([
                Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
        ];
    }
}
