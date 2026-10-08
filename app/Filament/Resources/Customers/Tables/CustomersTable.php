<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Actions\Marketing\SendBulkCustomerMessage;
use App\Actions\Marketing\UnsubscribeCustomerFromMarketing;
use App\Builders\Customers\CustomerQueryBuilder;
use App\Enums\Customers\CustomerStatus;
use App\Enums\Customers\MarketingSubscription;
use App\Enums\Marketing\BulkMessagePurpose;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Models\Customers\Customer;
use App\Services\Customers\BirthdayCalculator;
use App\Support\PhoneNumber;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $atRiskDays = self::atRiskDays();

        return $table
            ->modifyQueryUsing(fn (CustomerQueryBuilder $query): CustomerQueryBuilder => $query->withOrderMetrics())
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->formatStateUsing(fn (?string $state): string => PhoneNumber::display($state))
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
                    ->color(fn (Customer $record): string => resolve(BirthdayCalculator::class)->isToday($record->birthday) ? 'success' : 'gray')
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
                    ->getStateUsing(fn (Customer $record): int|float => $record->revenue_orders_count > 0
                        ? ($record->orders_sum_total / $record->revenue_orders_count)
                        : 0)
                    ->money('USD')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Customer $record): CustomerStatus => CustomerStatus::resolve(
                        (int) $record->orders_count,
                        $record->last_order_date ? Date::parse($record->last_order_date) : null,
                        $atRiskDays,
                    ))
                    ->toggleable(),

                TextColumn::make('email_marketing')
                    ->label('Email marketing')
                    ->badge()
                    ->getStateUsing(fn (Customer $record): MarketingSubscription => MarketingSubscription::resolve($record->marketing_opted_out_at))
                    ->formatStateUsing(fn (MarketingSubscription $state, Customer $record): string => $state->labelSince($record->marketing_opted_out_at))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('at_risk')
                    ->label('At Risk')
                    ->query(fn (Builder $query) => $query->whereHas('orders')
                        ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays($atRiskDays)))),

                SelectFilter::make('email_marketing')
                    ->label('Email marketing')
                    ->options(MarketingSubscription::class)
                    ->query(function (CustomerQueryBuilder $query, array $data): CustomerQueryBuilder {
                        $value = $data['value'] ?? null;

                        return match (is_string($value) ? MarketingSubscription::tryFrom($value) : null) {
                            MarketingSubscription::Subscribed => $query->subscribedToMarketing(),
                            MarketingSubscription::Unsubscribed => $query->unsubscribedFromMarketing(),
                            null => $query,
                        };
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
                Action::make('markUnsubscribed')
                    ->label('Mark unsubscribed')
                    ->icon(Heroicon::OutlinedBellSlash)
                    ->color('gray')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as unsubscribed')
                    ->modalDescription('Use this for opt-outs received by phone or email. They will stop getting marketing emails; order messages are unaffected. Only the customer can subscribe again, from the link in their emails.')
                    ->visible(fn (Customer $record): bool => $record->marketing_opted_out_at === null)
                    ->action(fn (Customer $record) => resolve(UnsubscribeCustomerFromMarketing::class)($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('sendMessage')
                        ->label('Send message')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->color('primary')
                        ->modalHeading('Send a message to selected customers')
                        ->modalDescription('Sends a one-off email to each selected customer who has an email address. Choose whether it is an order update or a promotion; promotions skip customers who unsubscribed from marketing emails. No campaign record or open tracking.')
                        ->modalSubmitActionLabel('Queue messages')
                        ->schema([
                            Radio::make('purpose')
                                ->options(BulkMessagePurpose::class)
                                ->required(),
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

                            $purpose = $data['purpose'] ?? null;

                            if (! $purpose instanceof BulkMessagePurpose) {
                                return;
                            }

                            $outcome = resolve(SendBulkCustomerMessage::class)(
                                $customers,
                                $purpose,
                                messageSubject: Arr::string($data, 'subject'),
                                body: Arr::string($data, 'body'),
                            );

                            Notification::make()
                                ->title($outcome->summary($purpose))
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
