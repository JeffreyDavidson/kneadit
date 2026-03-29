<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\PayPalService;
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

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['customer', 'user']))
            ->columns([
                TextColumn::make('order_number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->sortable()
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => OrderStatus::Pending->value,
                        'warning' => OrderStatus::Confirmed->value,
                        'info' => OrderStatus::Baking->value,
                        'success' => OrderStatus::Ready->value,
                        'primary' => OrderStatus::Delivered->value,
                        'danger' => OrderStatus::Cancelled->value,
                    ]),

                BadgeColumn::make('payment_status')
                    ->label('Payment')
                    ->colors([
                        'danger' => PaymentStatus::Unpaid->value,
                        'success' => PaymentStatus::Paid->value,
                        'gray' => PaymentStatus::Cancelled->value,
                        'warning' => PaymentStatus::Refunded->value,
                    ]),

                TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Baker')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),

                SelectFilter::make('payment_status')
                    ->options(PaymentStatus::class),

                Filter::make('delivery_date')
                    ->schema([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, string $date) => $query->whereDate('delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, string $date) => $query->whereDate('delivery_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'From ' . \Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Until ' . \Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Order')
                    ->modalDescription('Are you sure you want to confirm this order?')
                    ->action(function (Order $record) {
                        resolve(TransitionOrderStatus::class)($record, OrderStatus::Confirmed);
                        Notification::make()
                            ->title('Order confirmed')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => in_array(OrderStatus::Confirmed, TransitionOrderStatus::allowedTransitions($record))),

                Action::make('start_baking')
                    ->label('Start Baking')
                    ->icon(Heroicon::OutlinedFire)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Start Baking')
                    ->modalDescription('Mark this order as currently being baked?')
                    ->action(function (Order $record) {
                        resolve(TransitionOrderStatus::class)($record, OrderStatus::Baking);
                        Notification::make()
                            ->title('Order marked as baking')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => in_array(OrderStatus::Baking, TransitionOrderStatus::allowedTransitions($record))),

                Action::make('mark_ready')
                    ->label('Mark Ready')
                    ->icon(Heroicon::OutlinedClock)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Ready')
                    ->modalDescription('Mark this order as ready for pickup/delivery?')
                    ->action(function (Order $record) {
                        resolve(TransitionOrderStatus::class)($record, OrderStatus::Ready);
                        Notification::make()
                            ->title('Order marked as ready')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => in_array(OrderStatus::Ready, TransitionOrderStatus::allowedTransitions($record))),

                Action::make('mark_delivered')
                    ->label('Mark Delivered')
                    ->icon(Heroicon::OutlinedTruck)
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Mark Delivered')
                    ->modalDescription('Mark this order as delivered/completed?')
                    ->action(function (Order $record) {
                        resolve(TransitionOrderStatus::class)($record, OrderStatus::Delivered);
                        Notification::make()
                            ->title('Order marked as delivered')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Order $record) => in_array(OrderStatus::Delivered, TransitionOrderStatus::allowedTransitions($record))),

                Action::make('cancel')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Order')
                    ->modalDescription('Are you sure you want to cancel this order? This action cannot be undone.')
                    ->action(function (Order $record) {
                        resolve(TransitionOrderStatus::class)($record, OrderStatus::Cancelled);
                        Notification::make()
                            ->title('Order cancelled')
                            ->warning()
                            ->send();
                    })
                    ->visible(fn (Order $record) => in_array(OrderStatus::Cancelled, TransitionOrderStatus::allowedTransitions($record))),

                Action::make('send_paypal_invoice')
                    ->label('Send PayPal Invoice')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Send PayPal Invoice')
                    ->modalDescription('This will create and send a PayPal invoice to the customer for payment.')
                    ->action(function (Order $record) {
                        $paypalService = resolve(PayPalService::class);
                        $invoiceId = $paypalService->createAndSendInvoice($record);

                        if ($invoiceId) {
                            Notification::make()
                                ->title('PayPal invoice sent successfully')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Failed to send PayPal invoice')
                                ->body('Please check the logs for more details.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Order $record) => $record->payment_status === PaymentStatus::Unpaid &&
                        ! $record->paypal_invoice_id &&
                        in_array($record->status, [OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready]),
                    ),

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
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders will appear here as customers place them.');
    }
}
