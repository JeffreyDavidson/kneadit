<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Actions\Marketing\SendBulkCustomerMessage;
use App\Builders\Customers\CustomerQueryBuilder;
use App\Enums\Customers\CustomerStatus;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Customers\Customer;
use App\Services\Customers\BirthdayCalculator;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $atRiskDays = self::atRiskDays();

        return $table
            ->modifyQueryUsing(fn (CustomerQueryBuilder $query) => $query->withOrderMetrics())
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('full_address')
                    ->label('Address')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        if (Str::length($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),

                TextColumn::make('birthday')
                    ->label('Birthday')
                    ->date('M j')
                    ->badge()
                    ->color(fn (Customer $record) => resolve(BirthdayCalculator::class)->isToday($record->birthday) ? 'success' : 'gray')
                    ->icon(fn (Customer $record): ?Heroicon => resolve(BirthdayCalculator::class)->isToday($record->birthday) ? Heroicon::OutlinedCake : null)
                    ->formatStateUsing(fn (mixed $state, Customer $record): string => self::birthdayLabel($state, $record))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('orders_sum_total')
                    ->label('Lifetime Value')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('orders_count')
                    ->label('Orders')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('average_order_value')
                    ->label('Avg Order')
                    ->getStateUsing(fn (Customer $record) => $record->orders_count > 0
                        ? ($record->orders_sum_total / $record->orders_count)
                        : 0)
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Customer $record) => CustomerStatus::resolve(
                        (int) $record->orders_count,
                        $record->last_order_date ? Date::parse($record->last_order_date) : null,
                        $atRiskDays,
                    ))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('at_risk')
                    ->label('At Risk')
                    ->query(function (Builder $query) use ($atRiskDays) {
                        return $query->whereHas('orders')
                            ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($atRiskDays)));
                    }),

                Filter::make('has_birthday_this_month')
                    ->label('Birthday This Month')
                    ->query(
                        fn (Builder $query) => $query->whereNotNull('birthday')
                            ->whereMonth('birthday', now()->month),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('sendMessage')
                        ->label('Send message')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->color('primary')
                        ->modalHeading('Send a message to selected customers')
                        ->modalDescription('Drafts a one-off email to each selected customer who has an email address. No campaign record or open tracking — for ad-hoc operational messages.')
                        ->modalSubmitActionLabel('Queue messages')
                        ->schema([
                            TextInput::make('subject')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('body')
                                ->required()
                                ->rows(8)
                                ->helperText('Plain text. Line breaks are preserved.'),
                        ])
                        ->action(function (array $data, EloquentCollection $records): void {
                            /** @var array<int, Customer> $customers */
                            $customers = $records->all();

                            $sent = resolve(SendBulkCustomerMessage::class)(
                                $customers,
                                messageSubject: $data['subject'],
                                body: $data['body'],
                            );

                            Notification::make()
                                ->title("Message queued to {$sent} recipient(s)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No customers yet')
            ->emptyStateDescription('Customers will appear here once they place their first order.');
    }

    private static function atRiskDays(): int
    {
        return Config::integer('analytics.at_risk_threshold_days', 30);
    }

    private static function birthdayLabel(mixed $state, Customer $customer): string
    {
        if (resolve(BirthdayCalculator::class)->isToday($customer->birthday)) {
            return 'Today!';
        }

        if (! is_string($state) && ! $state instanceof \DateTimeInterface) {
            return '—';
        }

        return Date::parse($state)->format('M j');
    }
}
