<?php

namespace App\Filament\Resources\WaitlistEntryResource\Tables;

use App\Actions\Customers\UpdateWaitlistEntryStatus;
use App\Enums\Customers\WaitlistStatus;
use App\Models\Customers\WaitlistEntry;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaitlistEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product']))
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
                        'warning' => WaitlistStatus::Waiting->value,
                        'info' => WaitlistStatus::Notified->value,
                        'success' => WaitlistStatus::Converted->value,
                        'danger' => WaitlistStatus::Removed->value,
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WaitlistStatus::class),

                Filter::make('requested_date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, string $date) => $query->whereDate('requested_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, string $date) => $query->whereDate('requested_date', '<=', $date),
                            );
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
                Action::make('notify')
                    ->icon(Heroicon::OutlinedBell)
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (WaitlistEntry $record) {
                        resolve(UpdateWaitlistEntryStatus::class)($record, WaitlistStatus::Notified);
                        Notification::make()
                            ->title('Customer notified')
                            ->body('The customer has been marked as notified.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (WaitlistEntry $record) => $record->status === WaitlistStatus::Waiting),

                Action::make('convert')
                    ->icon(Heroicon::OutlinedCheck)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (WaitlistEntry $record) {
                        resolve(UpdateWaitlistEntryStatus::class)($record, WaitlistStatus::Converted);
                        Notification::make()
                            ->title('Customer converted')
                            ->body('The customer has been marked as converted.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (WaitlistEntry $record) => in_array($record->status, [WaitlistStatus::Waiting, WaitlistStatus::Notified])),

                Action::make('remove')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (WaitlistEntry $record) {
                        resolve(UpdateWaitlistEntryStatus::class)($record, WaitlistStatus::Removed);
                        Notification::make()
                            ->title('Customer removed')
                            ->body('The customer has been removed from the waitlist.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (WaitlistEntry $record) => $record->status !== WaitlistStatus::Removed),

                EditAction::make()
                    ->slideOver()
                    ->modalWidth('md'),
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
