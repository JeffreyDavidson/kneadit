<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Models\Customers\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('message')
                    ->limit(100)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        if (Str::length($state) <= 100) {
                            return null;
                        }

                        return $state;
                    }),

                IconColumn::make('is_read')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('toggleRead')
                    ->label(fn (ContactMessage $record): string => $record->is_read ? 'Mark Unread' : 'Mark Read')
                    ->icon(fn (ContactMessage $record): Heroicon => $record->is_read ? Heroicon::OutlinedEnvelope : Heroicon::OutlinedEnvelopeOpen)
                    ->color(fn (ContactMessage $record): string => $record->is_read ? 'gray' : 'success')
                    ->action(function (ContactMessage $record): void {
                        $record->update(['is_read' => ! $record->is_read]);

                        Notification::make()
                            ->title($record->is_read ? 'Marked as read' : 'Marked as unread')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No messages yet')
            ->emptyStateDescription('Customer messages will appear here.');
    }
}
