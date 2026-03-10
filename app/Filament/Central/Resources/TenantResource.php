<?php

namespace App\Filament\Central\Resources;

use App\Models\Tenant;
use BackedEnum;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;

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
                                ->type('color')
                                ->placeholder('#d4920c'),
                            TextInput::make('brand_color_secondary')
                                ->label('Secondary Color')
                                ->type('color')
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
                            DateTimePicker::make('trial_ends_at')
                                ->label('Trial Ends')
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
                        'starter' => 'warning',
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
                Actions\ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->slideOver(),
                    Actions\Action::make('impersonate')
                        ->label('Login as Baker')
                        ->icon('heroicon-o-finger-print')
                        ->color('warning')
                        ->url(fn (Tenant $record) => URL::signedRoute('tenant.impersonate', ['tenant' => $record->id]))
                        ->openUrlInNewTab(),
                    Actions\Action::make('visit')
                        ->label('Visit Storefront')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Tenant $record) => 'https://' . $record->id . '.getkneadit.app')
                        ->openUrlInNewTab(),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('enable_storefront')
                    ->label('Enable Storefront')
                    ->icon('heroicon-o-building-storefront')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['storefront_enabled' => true]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('disable_storefront')
                    ->label('Disable Storefront')
                    ->icon('heroicon-o-building-storefront')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['storefront_enabled' => false]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('extend_trial')
                    ->label('Extend Trial 30 Days')
                    ->icon('heroicon-o-clock')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['trial_ends_at' => now()->addDays(30)]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('change_plan')
                    ->label('Change Plan')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Select::make('plan')
                            ->label('New Plan')
                            ->options([
                                'starter' => 'Starter ($9/mo)',
                                'growth' => 'Growth ($19/mo)',
                                'pro' => 'Pro ($29/mo)',
                            ])
                            ->required(),
                    ])
                    ->action(fn (Collection $records, array $data) => $records->each->update(['plan' => $data['plan']]))
                    ->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Store Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('store_name')
                                ->label('Bakery Name')
                                ->size('lg')
                                ->weight('bold')
                                ->placeholder('Not set'),
                            TextEntry::make('id')
                                ->label('Subdomain URL')
                                ->formatStateUsing(fn (string $state) => $state . '.getkneadit.app')
                                ->url(fn (Tenant $record) => 'https://' . $record->id . '.getkneadit.app')
                                ->openUrlInNewTab(),
                            TextEntry::make('custom_domain')
                                ->label('Custom Domain')
                                ->placeholder('None'),
                            TextEntry::make('external_website')
                                ->label('External Website')
                                ->url(fn (?string $state) => $state)
                                ->openUrlInNewTab()
                                ->placeholder('None'),
                        ]),
                    ]),

                Section::make('Owner Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Owner Name'),
                            TextEntry::make('email')
                                ->label('Email')
                                ->copyable(),
                        ]),
                    ]),

                Section::make('Plan & Status')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('plan')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'starter' => 'warning',
                                    'growth' => 'info',
                                    'pro' => 'success',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state) => ucfirst($state)),
                            TextEntry::make('is_active')
                                ->label('Active')
                                ->badge()
                                ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                                ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                            TextEntry::make('storefront_enabled')
                                ->label('Storefront')
                                ->badge()
                                ->formatStateUsing(fn (bool $state) => $state ? 'Enabled' : 'Disabled')
                                ->color(fn (bool $state) => $state ? 'success' : 'danger'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('trial_ends_at')
                                ->label('Trial Ends')
                                ->dateTime()
                                ->placeholder('No trial'),
                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime(),
                        ]),
                    ]),

                Section::make('Branding')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('brand_color_primary')
                                ->label('Primary Color')
                                ->placeholder('Not set'),
                            TextEntry::make('brand_color_secondary')
                                ->label('Secondary Color')
                                ->placeholder('Not set'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Central\Resources\TenantResource\RelationManagers\NotesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Central\Resources\TenantResource\Pages\ListTenants::route('/'),
            'view' => \App\Filament\Central\Resources\TenantResource\Pages\ViewTenant::route('/{record}'),
        ];
    }
}
