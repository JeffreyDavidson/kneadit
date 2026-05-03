<?php

namespace App\Filament\Central\Resources\ImpersonationTokens\Tables;

use App\Models\Platform\ImpersonationToken;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImpersonationTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['tenant', 'createdBy']))
            ->columns([
                TextColumn::make('tenant.store_name')
                    ->label('Bakery')
                    ->placeholder('Not set')
                    ->description(fn (ImpersonationToken $record) => $record->tenant_id)
                    ->searchable(),
                TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Issued')
                    ->dateTime('M j, Y · g:i A')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(function (ImpersonationToken $record): string {
                        if ($record->consumed_at) {
                            return 'consumed';
                        }
                        if ($record->expires_at->isPast()) {
                            return 'expired';
                        }

                        return 'pending';
                    })
                    ->color(fn (string $state) => match ($state) {
                        'consumed' => 'success',
                        'pending' => 'info',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('consumed_at')
                    ->label('Consumed')
                    ->dateTime('M j, Y · g:i A')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('consumer_ip')
                    ->label('IP')
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Filter::make('consumed')
                    ->label('Consumed only')
                    ->query(fn (Builder $query) => $query->whereNotNull('consumed_at')),
                Filter::make('pending')
                    ->label('Pending only')
                    ->query(fn (Builder $query) => $query->whereNull('consumed_at')->where('expires_at', '>', now())),
                Filter::make('expired')
                    ->label('Expired only')
                    ->query(fn (Builder $query) => $query->whereNull('consumed_at')->where('expires_at', '<=', now())),
            ])
            ->recordActions([]);
    }
}
