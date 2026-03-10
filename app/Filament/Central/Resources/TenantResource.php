<?php

namespace App\Filament\Central\Resources;

use App\Models\Tenant;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Bakeries';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Owner Information')
                    ->description('The person who owns this bakery account')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('id')
                                ->label('Subdomain / ID')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->alphaDash()
                                ->placeholder('sweet-bakes')
                                ->helperText('This becomes their subdomain: sweet-bakes.getkneadit.app')
                                ->disabled(fn ($record) => $record !== null),
                            TextInput::make('name')
                                ->label('Owner Name')
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->email()
                                ->required(),
                            Select::make('plan')
                                ->options([
                                    'starter' => 'Starter ($9/mo)',
                                    'growth' => 'Growth ($19/mo)',
                                    'pro' => 'Pro ($29/mo)',
                                ])
                                ->required(),
                        ]),
                    ]),

                Section::make('Store Details')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('store_name')
                                ->label('Bakery Name')
                                ->placeholder('Sweet Bakes Co.'),
                            TextInput::make('custom_domain')
                                ->label('Custom Domain')
                                ->placeholder('sweetbakes.com')
                                ->helperText('Optional — baker can use their own domain'),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('brand_color_primary')
                                ->label('Primary Color')
                                ->placeholder('#d4920c'),
                            TextInput::make('brand_color_secondary')
                                ->label('Secondary Color')
                                ->placeholder('#1c1410'),
                        ]),
                        TextInput::make('external_website')
                            ->label('External Website')
                            ->url()
                            ->placeholder('https://sweetbakes.com')
                            ->helperText('If baker has their own site (storefront skip mode)'),
                    ]),

                Section::make('Status')
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true),
                            Toggle::make('storefront_enabled')
                                ->label('Storefront Enabled')
                                ->default(true),
                            TextInput::make('trial_ends_at')
                                ->label('Trial Ends')
                                ->disabled()
                                ->placeholder('N/A'),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Subdomain')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn (Tenant $record) => $record->id . '.getkneadit.app'),

                TextColumn::make('store_name')
                    ->label('Bakery')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not set'),

                TextColumn::make('name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('plan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'starter' => 'gray',
                        'growth' => 'info',
                        'pro' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('storefront_enabled')
                    ->label('Storefront')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('trial_ends_at')
                    ->label('Trial Ends')
                    ->date()
                    ->sortable()
                    ->placeholder('No trial')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->options([
                        'starter' => 'Starter',
                        'growth' => 'Growth',
                        'pro' => 'Pro',
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('storefront_enabled')
                    ->label('Storefront'),
            ])
            ->actions([
                EditAction::make(),
                \Filament\Actions\Action::make('visit')
                    ->label('Visit')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Tenant $record) => 'http://' . $record->id . '.getkneadit.app')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Central\Resources\TenantResource\Pages\ListTenants::route('/'),
            'create' => \App\Filament\Central\Resources\TenantResource\Pages\CreateTenant::route('/create'),
            'edit' => \App\Filament\Central\Resources\TenantResource\Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
