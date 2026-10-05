<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Actions\Orders\MarkOrderPaid;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\CancelOrderAction;
use App\Filament\Actions\RefundOrderAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Filters\DateRangeFilter;
use App\Models\Orders\Order;
use App\Services\PayPal\InvoiceService;
use App\Services\PayPal\TokenManager;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
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
                CancelOrderAction::make(),
                RefundOrderAction::make(),
                self::markPaidAction(),

                Action::make('send_paypal_invoice')
                    ->label('Send PayPal Invoice')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->color('warning')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send PayPal Invoice')
                    ->modalDescription('This will create and send a PayPal invoice to the customer for payment.')
                    ->action(function (Order $record): void {
                        $invoiceId = resolve(InvoiceService::class)->createAndSend($record);

                        if ($invoiceId) {
                            Notification::make()
                                ->title('PayPal invoice sent successfully')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Failed to send PayPal invoice')
                            ->body('Check Settings → PayPal that the credentials are correct, then try again.')
                            ->danger()
                            ->send();
                    })
                    ->visible(
                        fn (Order $record): bool => $record->payment_status === PaymentStatus::Unpaid &&
                        ! $record->paypal_invoice_id &&
                        in_array($record->status, [OrderStatus::Confirmed, OrderStatus::Baking, OrderStatus::Ready]) &&
                        // Hide entirely when PayPal isn't configured for the tenant
                        // (no client_id/client_secret in settings or env). Avoids
                        // showing a button that will silently fail with a generic
                        // auth error on click.
                        resolve(TokenManager::class)->isConfigured(),
                    ),

                SlideOverEditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    AuthorizedDeleteBulkAction::make()
                        ->missingBulkAuthorizationFailureNotificationMessage(
                            fn (int $failureCount): string => "{$failureCount} not deleted. Only managers can delete orders, and only pending or cancelled orders with no payment or refund. Cancel the rest instead.",
                        ),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders will appear here as customers place them.');
    }

    /**
     * Record a payment taken outside checkout (typically cash). Goes through
     * MarkOrderPaid so the payment is logged and non-manual payment methods
     * auto-confirm a pending order.
     */
    private static function markPaidAction(): Action
    {
        return Action::make('markPaid')
            ->label('Mark Paid')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('success')
            ->authorize('update')
            ->requiresConfirmation()
            ->modalHeading('Mark Order Paid')
            ->modalDescription('Record that payment for this order has been received?')
            ->action(function (Order $record): void {
                resolve(MarkOrderPaid::class)($record);

                Notification::make()
                    ->title('Order marked as paid')
                    ->success()
                    ->send();
            })
            ->visible(fn (Order $record): bool => $record->payment_status->canBeMarkedPaid() && $record->status !== OrderStatus::Cancelled);
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
            ->action(function (Order $record) use ($targetStatus, $notificationTitle, $notificationColor): void {
                resolve(TransitionOrderStatus::class)($record, $targetStatus);
                Notification::make()
                    ->title($notificationTitle)
                    ->color($notificationColor)
                    ->send();
            })
            ->visible(fn (Order $record): bool => in_array($targetStatus, TransitionOrderStatus::allowedTransitions($record)));
    }
}
