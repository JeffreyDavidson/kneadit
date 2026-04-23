<?php

namespace App\Filament\Resources\CateringInquiries\Tables;

use App\Actions\Customers\RecordCateringDeposit;
use App\Actions\Customers\TransitionCateringInquiryStatus;
use App\Enums\Customers\CateringInquiryStatus;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Tables\Columns\MoneyColumn;
use App\Models\Customers\CateringInquiry;
use App\Services\Settings\TenantSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
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
                MoneyColumn::make('quoted_amount')
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
                    ->options(function () {
                        $types = app(TenantSettings::class)->catering->eventTypes;

                        return array_combine($types, $types);
                    }),
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
                    ->modalDescription(fn (CateringInquiry $record) => "Send a quote of {$record->quoted_amount?->formatted()} to {$record->customer_email}?")
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
                Action::make('record_deposit')
                    ->label('Mark Deposit Received')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->color('success')
                    ->authorize('update')
                    ->visible(fn (CateringInquiry $record) => $record->deposit_paid_at === null && in_array($record->status, [CateringInquiryStatus::Quoted, CateringInquiryStatus::Confirmed]))
                    ->schema(fn (CateringInquiry $record) => [
                        TextInput::make('amount')
                            ->label('Deposit Amount ($)')
                            ->numeric()
                            ->required()
                            ->default(fn () => resolve(RecordCateringDeposit::class)->suggestedAmount(
                                $record,
                                app(TenantSettings::class)->catering->depositPercent,
                            )),
                        TextInput::make('reference')
                            ->label('Reference (check #, last-4, etc.)')
                            ->maxLength(255),
                    ])
                    ->action(function (CateringInquiry $record, array $data): void {
                        resolve(RecordCateringDeposit::class)(
                            $record,
                            (float) $data['amount'],
                            $data['reference'] ?? null,
                        );
                    }),
                SlideOverEditAction::make(),
            ])
            ->emptyStateHeading('No catering inquiries yet')
            ->emptyStateDescription('Inquiries will appear here when customers submit them.');
    }
}
