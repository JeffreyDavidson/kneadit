<?php

namespace App\Filament\Resources\WaitlistEntries\Tables;

use App\Models\WaitlistEntry;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WaitlistEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['product']))
            ->columns([
                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_email')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('customer_phone')
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Product')
                    ->limit(20)
                    ->placeholder('Any Product'),

                TextColumn::make('requested_date')
                    ->date()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'notified',
                        'success' => 'converted',
                        'danger' => 'removed',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WaitlistEntry::STATUSES),

                Filter::make('requested_date')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('requested_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('requested_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('notify')
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (WaitlistEntry $record) {
                        $record->markNotified();
                        Notification::make()
                            ->title('Customer notified')
                            ->body('The customer has been marked as notified.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (WaitlistEntry $record) => $record->status === 'waiting'),

                Action::make('convert')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (WaitlistEntry $record) {
                        $record->markConverted();
                        Notification::make()
                            ->title('Customer converted')
                            ->body('The customer has been marked as converted.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (WaitlistEntry $record) => in_array($record->status, ['waiting', 'notified'])),

                Action::make('remove')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (WaitlistEntry $record) {
                        $record->markRemoved();
                        Notification::make()
                            ->title('Customer removed')
                            ->body('The customer has been removed from the waitlist.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (WaitlistEntry $record) => $record->status !== 'removed'),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No waitlist entries found')
            ->emptyStateDescription('Start managing your customer waitlist by creating your first entry.');
    }
}
