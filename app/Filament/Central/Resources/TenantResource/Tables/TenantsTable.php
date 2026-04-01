<?php

namespace App\Filament\Central\Resources\TenantResource\Tables;

use App\Models\Platform\Tenant;
use Filament\Actions;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\URL;

class TenantsTable
{
    public static function configure(Table $table): Table
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
                        ->icon(Heroicon::OutlinedFingerPrint)
                        ->color('warning')
                        ->url(fn (Tenant $record) => URL::signedRoute('tenant.impersonate', ['tenant' => $record->id]))
                        ->openUrlInNewTab(),
                    Actions\Action::make('visit')
                        ->label('Visit Storefront')
                        ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Tenant $record) => 'https://' . $record->id . '.getkneadit.app')
                        ->openUrlInNewTab(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                BulkAction::make('activate')
                    ->label('Activate')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('deactivate')
                    ->label('Deactivate')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('enable_storefront')
                    ->label('Enable Storefront')
                    ->icon(Heroicon::OutlinedBuildingStorefront)
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['storefront_enabled' => true]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('disable_storefront')
                    ->label('Disable Storefront')
                    ->icon(Heroicon::OutlinedBuildingStorefront)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['storefront_enabled' => false]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('extend_trial')
                    ->label('Extend Trial 30 Days')
                    ->icon(Heroicon::OutlinedClock)
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['trial_ends_at' => now()->addDays(config('kneadit.trial_days', 30))]))
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('change_plan')
                    ->label('Change Plan')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->schema([
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
}
