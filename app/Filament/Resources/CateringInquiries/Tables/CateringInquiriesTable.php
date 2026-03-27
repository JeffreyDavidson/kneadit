<?php

namespace App\Filament\Resources\CateringInquiries\Tables;

use App\Enums\CateringEventType;
use App\Enums\CateringInquiryStatus;
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
                    ->color(fn (mixed $state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        CateringEventType::Wedding->value => 'danger',
                        CateringEventType::Corporate->value => 'info',
                        CateringEventType::Birthday->value => 'warning',
                        CateringEventType::Holiday->value => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (mixed $state): string => ($state instanceof CateringEventType ? $state->label() : CateringEventType::tryFrom($state)?->label()) ?? ucfirst($state)),
                TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('guest_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (mixed $state): string => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        CateringInquiryStatus::Inquiry->value => 'gray',
                        CateringInquiryStatus::Quoted->value => 'info',
                        CateringInquiryStatus::Confirmed->value => 'success',
                        CateringInquiryStatus::Completed->value => 'primary',
                        CateringInquiryStatus::Cancelled->value => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (mixed $state): string => ($state instanceof CateringInquiryStatus ? $state->label() : CateringInquiryStatus::tryFrom($state)?->label()) ?? ucfirst($state)),
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
                    ->options(CateringInquiryStatus::class),
                SelectFilter::make('event_type')
                    ->options(CateringEventType::class),
                Filter::make('event_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, string $date) => $q->whereDate('event_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, string $date) => $q->whereDate('event_date', '<=', $date));
                    }),
            ])
            ->recordActions([
                Action::make('send_quote')
                    ->label('Send Quote')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Send Quote to Customer')
                    ->modalDescription(fn (CateringInquiry $record) => "Send a quote of \${$record->quoted_amount} to {$record->customer_email}?")
                    ->visible(fn (CateringInquiry $record) => $record->quoted_amount && in_array($record->status, [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Quoted]))
                    ->action(function (CateringInquiry $record) {
                        Mail::to($record->customer_email)->send(new CateringQuote($record));
                        $record->update(['status' => CateringInquiryStatus::Quoted]);
                    }),
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (CateringInquiry $record) => $record->status === CateringInquiryStatus::Quoted)
                    ->action(fn (CateringInquiry $record) => $record->update(['status' => CateringInquiryStatus::Confirmed])),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ]);
    }
}
