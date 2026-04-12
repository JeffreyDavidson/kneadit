<?php

namespace App\Filament\Central\Resources\SupportTicketResource\Tables;

use App\Enums\Platform\SupportTicketPriority;
use App\Enums\Platform\SupportTicketStatus;
use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SupportTicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tenant.store_name')
                    ->label('Bakery')
                    ->placeholder('Not set')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (SupportTicketStatus $state): string => match ($state) {
                        SupportTicketStatus::Open => 'danger',
                        SupportTicketStatus::InProgress => 'warning',
                        SupportTicketStatus::Resolved => 'success',
                        SupportTicketStatus::Closed => 'gray',
                    }),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (SupportTicketPriority $state): string => match ($state) {
                        SupportTicketPriority::High => 'danger',
                        SupportTicketPriority::Normal => 'info',
                        SupportTicketPriority::Low => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('status', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options(SupportTicketStatus::class),
                SelectFilter::make('priority')
                    ->options(SupportTicketPriority::class),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make()
                    ->slideOver(),
            ]);
    }
}
