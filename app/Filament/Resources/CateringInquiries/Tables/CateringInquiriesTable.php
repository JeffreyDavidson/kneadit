<?php

namespace App\Filament\Resources\CateringInquiries\Tables;

use App\Actions\Customers\TransitionCateringInquiryStatus;
use App\Enums\Customers\CateringEventType;
use App\Enums\Customers\CateringInquiryStatus;
use App\Filament\Filters\DateRangeFilter;
use App\Models\Customers\CateringInquiry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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
                    ->badge(),
                TextColumn::make('event_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('guest_count')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
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
                DateRangeFilter::make('event_date'),
            ])
            ->recordActions([
                Action::make('send_quote')
                    ->label('Send Quote')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('info')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send Quote to Customer')
                    ->modalDescription(fn (CateringInquiry $record) => "Send a quote of \${$record->quoted_amount} to {$record->customer_email}?")
                    ->visible(fn (CateringInquiry $record) => $record->quoted_amount && in_array($record->status, [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Quoted]))
                    ->action(fn (CateringInquiry $record) => resolve(TransitionCateringInquiryStatus::class)($record, CateringInquiryStatus::Quoted)),
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->visible(fn (CateringInquiry $record) => $record->status === CateringInquiryStatus::Quoted)
                    ->action(fn (CateringInquiry $record) => resolve(TransitionCateringInquiryStatus::class)($record, CateringInquiryStatus::Confirmed)),
                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
            ])
            ->emptyStateHeading('No catering inquiries yet')
            ->emptyStateDescription('Inquiries will appear here when customers submit them.');
    }
}
