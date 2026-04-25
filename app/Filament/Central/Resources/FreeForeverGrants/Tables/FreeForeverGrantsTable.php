<?php

namespace App\Filament\Central\Resources\FreeForeverGrants\Tables;

use App\Models\Platform\FreeForeverGrant;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FreeForeverGrantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.store_name')
                    ->label('Bakery')
                    ->placeholder('Not set')
                    ->description(fn (FreeForeverGrant $record) => $record->tenant_id)
                    ->searchable(),
                TextColumn::make('grantedByUser.name')
                    ->label('Granted By')
                    ->placeholder('—'),
                TextColumn::make('granted_at')
                    ->label('Granted')
                    ->dateTime('M j, Y · g:i A')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (FreeForeverGrant $record) => $record->revoked_at ? 'revoked' : 'active')
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'revoked' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('revoked_at')
                    ->label('Revoked')
                    ->dateTime('M j, Y · g:i A')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('granted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'revoked' => 'Revoked',
                    ])
                    ->query(function ($query, array $data) {
                        if (($data['value'] ?? null) === 'active') {
                            $query->whereNull('revoked_at');
                        } elseif (($data['value'] ?? null) === 'revoked') {
                            $query->whereNotNull('revoked_at');
                        }
                    }),
            ])
            ->recordActions([
                Action::make('revoke')
                    ->label('Revoke')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->color('danger')
                    ->authorize('platform-admin')
                    ->visible(fn (FreeForeverGrant $record) => $record->revoked_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Revoke free-forever access')
                    ->modalDescription('The tenant will start seeing billing prompts again and will need an active Stripe subscription or trial to use the service.')
                    ->action(function (FreeForeverGrant $record): void {
                        $record->update(['revoked_at' => now()]);
                        $record->tenant->update(['free_forever' => false]);

                        Notification::make()
                            ->title('Grant revoked')
                            ->body('Tenant ' . ($record->tenant->store_name ?: $record->tenant_id) . ' is no longer free forever.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
