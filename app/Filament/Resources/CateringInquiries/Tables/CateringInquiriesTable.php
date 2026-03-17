<?php

namespace App\Filament\Resources\CateringInquiries\Tables;

use App\Mail\CateringQuote;
use App\Models\CateringInquiry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class CateringInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'wedding' => 'danger',
                        'corporate' => 'info',
                        'birthday' => 'warning',
                        'holiday' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('guest_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inquiry' => 'gray',
                        'quoted' => 'info',
                        'confirmed' => 'success',
                        'completed' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'inquiry' => 'New Inquiry',
                        'quoted' => 'Quote Sent',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        default => ucfirst($state),
                    }),
                TextColumn::make('quoted_amount')
                    ->money('usd')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'inquiry' => 'New Inquiry',
                        'quoted' => 'Quote Sent',
                        'confirmed' => 'Confirmed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('event_type')
                    ->options([
                        'wedding' => 'Wedding',
                        'corporate' => 'Corporate',
                        'birthday' => 'Birthday',
                        'holiday' => 'Holiday',
                        'other' => 'Other',
                    ]),
                Filter::make('event_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('event_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('event_date', '<=', $date));
                    }),
            ])
            ->actions([
                Action::make('send_quote')
                    ->label('Send Quote')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Send Quote to Customer')
                    ->modalDescription(fn (CateringInquiry $record) => "Send a quote of \${$record->quoted_amount} to {$record->customer_email}?")
                    ->visible(fn (CateringInquiry $record) => $record->quoted_amount && in_array($record->status, ['inquiry', 'quoted']))
                    ->action(function (CateringInquiry $record) {
                        Mail::to($record->customer_email)->send(new CateringQuote($record));
                        $record->update(['status' => 'quoted']);
                    }),
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CateringInquiry $record) => $record->status === 'quoted')
                    ->action(fn (CateringInquiry $record) => $record->update(['status' => 'confirmed'])),
                EditAction::make(),
            ]);
    }
}
