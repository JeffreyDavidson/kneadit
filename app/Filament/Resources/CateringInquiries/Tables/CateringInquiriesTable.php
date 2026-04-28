<?php

namespace App\Filament\Resources\CateringInquiries\Tables;

use App\Enums\Customers\CateringInquiryStatus;
use App\Filament\Filters\DateRangeFilter;
use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use App\Filament\Tables\Columns\MoneyColumn;
use App\Models\Customers\CateringInquiry;
use App\Services\Settings\TenantSettings;
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
                        $types = resolve(TenantSettings::class)->catering->eventTypes;

                        return array_combine($types, $types);
                    }),
                DateRangeFilter::make('event_date'),
            ])
            ->recordUrl(fn (CateringInquiry $record): string => CateringInquiryResource::getUrl('view', ['record' => $record]))
            ->emptyStateHeading('No catering inquiries yet')
            ->emptyStateDescription('Inquiries will appear here when customers submit them.');
    }
}
