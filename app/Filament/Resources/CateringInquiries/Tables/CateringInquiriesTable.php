<?php

namespace App\Filament\Resources\CateringInquiries\Tables;

use App\Actions\Customers\TransitionCateringInquiryStatus;
use App\Enums\CateringEventType;
use App\Enums\CateringInquiryStatus;
use App\Models\CateringInquiry;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                Filter::make('event_date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, string $date) => $q->whereDate('event_date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, string $date) => $q->whereDate('event_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . \Illuminate\Support\Facades\Date::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . \Illuminate\Support\Facades\Date::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('send_quote')
                    ->label('Send Quote')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Send Quote to Customer')
                    ->modalDescription(fn (CateringInquiry $record) => "Send a quote of \${$record->quoted_amount} to {$record->customer_email}?")
                    ->visible(fn (CateringInquiry $record) => $record->quoted_amount && in_array($record->status, [CateringInquiryStatus::Inquiry, CateringInquiryStatus::Quoted]))
                    ->action(fn (CateringInquiry $record) => resolve(TransitionCateringInquiryStatus::class)($record, CateringInquiryStatus::Quoted)),
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
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
