<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Filters\DateRangeFilter;
use App\Models\Orders\Order;
use App\Services\PayPal\InvoiceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
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

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('payment_status')
                    ->badge()
                    ->label('Payment'),

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

                DateRangeFilter::make('delivery_date'),
            ])
            ->recordActions([
                self::statusTransitionAction('confirm', OrderStatus::Confirmed, Heroicon::OutlinedCheckCircle, 'warning', 'Confirm Order', 'Are you sure you want to confirm this order?', 'Order confirmed'),
                self::statusTransitionAction('start_baking', OrderStatus::Baking, Heroicon::OutlinedFire, 'info', 'Start Baking', 'Mark this order as currently being baked?', 'Order marked as baking', 'Start Baking'),
                self::statusTransitionAction('mark_ready', OrderStatus::Ready, Heroicon::OutlinedClock, 'success', 'Mark Ready', 'Mark this order as ready for pickup/delivery?', 'Order marked as ready', 'Mark Ready'),
                self::statusTransitionAction('mark_delivered', OrderStatus::Delivered, Heroicon::OutlinedTruck, 'primary', 'Mark Delivered', 'Mark this order as delivered/completed?', 'Order marked as delivered', 'Mark Delivered'),
                self::statusTransitionAction('cancel', OrderStatus::Cancelled, Heroicon::OutlinedXCircle, 'danger', 'Cancel Order', 'Are you sure you want to cancel this order? This action cannot be undone.', 'Order cancelled', notificationColor: 'warning'),

                Action::make('send_paypal_invoice')
                    ->label('Send PayPal Invoice')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->color('warning')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send PayPal Invoice')
                    ->modalDescription('This will create and send a PayPal invoice to the customer for payment.')
                    ->action(function (Order $record) {
                        $invoiceService = resolve(InvoiceService::class);
                        $invoiceId = $invoiceService->createAndSend($record);

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
                    ->visible(
                        fn (Order $record) => $record->payment_status === PaymentStatus::Unpaid &&
                        ! $record->paypal_invoice_id &&
                        in_array($record->status, [OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready]),
                    ),

                SlideOverEditAction::make(),
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

    private static function statusTransitionAction(
        string $name,
        OrderStatus $targetStatus,
        Heroicon $icon,
        string $color,
        string $heading,
        string $description,
        string $notificationTitle,
        ?string $label = null,
        string $notificationColor = 'success',
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->authorize('update')
            ->requiresConfirmation()
            ->modalHeading($heading)
            ->modalDescription($description)
            ->action(function (Order $record) use ($targetStatus, $notificationTitle, $notificationColor) {
                resolve(TransitionOrderStatus::class)($record, $targetStatus);
                Notification::make()
                    ->title($notificationTitle)
                    ->color($notificationColor)
                    ->send();
            })
            ->visible(fn (Order $record) => in_array($targetStatus, TransitionOrderStatus::allowedTransitions($record)));
    }
}
