<?php

namespace App\Filament\Central\Resources\SupportTicketResource\Tables;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
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
                    ->color(fn (string $state): string => match ($state) {
                        SupportTicketStatus::Open->value => 'danger',
                        SupportTicketStatus::InProgress->value => 'warning',
                        SupportTicketStatus::Resolved->value => 'success',
                        SupportTicketStatus::Closed->value => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        SupportTicketPriority::High->value => 'danger',
                        SupportTicketPriority::Normal->value => 'info',
                        SupportTicketPriority::Low->value => 'gray',
                        default => 'gray',
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
