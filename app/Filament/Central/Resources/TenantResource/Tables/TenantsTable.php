<?php

namespace App\Filament\Central\Resources\TenantResource\Tables;

use App\Actions\Platform\PauseTenant;
use App\Actions\Platform\ResumeTenant;
use App\Enums\Platform\SubscriptionTier;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Models\Platform\FreeForeverGrant;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Filament\Actions;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
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
                    ->description(fn (Tenant $record, TenantUrlGenerator $urls): string => $urls->storefrontHost($record)),

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
                    ->sortable(),

                IconColumn::make('free_forever')
                    ->label('Free')
                    ->boolean()
                    ->tooltip('Platform-admin-granted free-forever access')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('storefront_enabled')
                    ->label('Storefront')
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_paused')
                    ->label('Paused')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->tooltip('Paused bakeries take no orders and send no customer emails')
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
                    ->options(SubscriptionTier::class),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                TernaryFilter::make('storefront_enabled')
                    ->label('Storefront'),
                TernaryFilter::make('paused')
                    ->label('Paused')
                    ->nullable()
                    ->attribute('paused_at'),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()->slideOver(),
                    Actions\Action::make('impersonate')
                        ->label('Login as Baker')
                        ->icon(Heroicon::OutlinedFingerPrint)
                        ->color('warning')
                        ->authorize('platform-admin')
                        ->url(fn (Tenant $record) => URL::signedRoute('tenant.impersonate', ['tenant' => $record->id]))
                        ->openUrlInNewTab(),
                    Actions\Action::make('visit')
                        ->label('Visit Storefront')
                        ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Tenant $record, TenantUrlGenerator $urls): string => $urls->storefront($record))
                        ->openUrlInNewTab(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Activate accounts')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('deactivate')
                        ->label('Deactivate accounts')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('resume')
                        ->label('Resume bakeries')
                        ->icon(Heroicon::OutlinedPlayCircle)
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records, ResumeTenant $resumeTenant) => $records->each(fn (Tenant $tenant) => $resumeTenant($tenant)))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('pause')
                        ->label('Pause bakeries')
                        ->icon(Heroicon::OutlinedPauseCircle)
                        ->color('danger')
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records, PauseTenant $pauseTenant) => $records->each(fn (Tenant $tenant) => $pauseTenant($tenant)))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('extend_trial')
                        ->label('Extend trial 30 days')
                        ->icon(Heroicon::OutlinedClock)
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['trial_ends_at' => now()->addDays(self::trialDays())]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('change_plan')
                        ->label('Change plan')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->authorize('platform-admin')
                        ->schema([
                            Select::make('plan')
                                ->label('New Plan')
                                ->options(collect(SubscriptionTier::cases())
                                    ->mapWithKeys(fn (SubscriptionTier $tier): array => [$tier->value => $tier->labelWithPrice()])
                                    ->all())
                                ->required(),
                        ])
                        ->action(fn (Collection $records, array $data) => $records->each->update(['plan' => $data['plan']]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('grant_free_forever')
                        ->label('Grant free forever')
                        ->icon(Heroicon::OutlinedGift)
                        ->color('success')
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->modalHeading('Grant free-forever access')
                        ->modalDescription('The selected tenants will bypass billing entirely and get full Pro-tier features. No card required, no trial expiry.')
                        ->action(function (Collection $records): void {
                            $actorId = Auth::id();
                            $records->each(function (Tenant $tenant) use ($actorId): void {
                                $tenant->update(['free_forever' => true]);
                                FreeForeverGrant::query()->create([
                                    'tenant_id' => $tenant->id,
                                    'granted_by_user_id' => $actorId,
                                    'granted_at' => now(),
                                ]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('revoke_free_forever')
                        ->label('Revoke free forever')
                        ->icon(Heroicon::OutlinedGift)
                        ->color('danger')
                        ->authorize('platform-admin')
                        ->requiresConfirmation()
                        ->modalHeading('Revoke free-forever access')
                        ->modalDescription('The selected tenants will start seeing billing prompts again and will need an active Stripe subscription or trial to use the service.')
                        ->action(function (Collection $records): void {
                            $records->each(function (Tenant $tenant): void {
                                $tenant->update(['free_forever' => false]);
                                FreeForeverGrant::query()
                                    ->where('tenant_id', $tenant->id)
                                    ->whereNull('revoked_at')
                                    ->update(['revoked_at' => now()]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                    AuthorizedDeleteBulkAction::make(),
                ])
                    ->label('Bulk actions')
                    ->icon(Heroicon::OutlinedBolt)
                    ->button(),
            ])
            // The delete policy and the subscription-cancelling listener both read the owner.
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('owner'))
            ->defaultSort('created_at', 'desc');
    }

    private static function trialDays(): int
    {
        return Config::integer('kneadit.trial_days', 30);
    }
}
