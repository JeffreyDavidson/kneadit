<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Actions\Orders\RefundStripePayment;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Filament\Actions\AuthorizedDeleteBulkAction;
use App\Filament\Actions\SlideOverEditAction;
use App\Filament\Filters\DateRangeFilter;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\PayPal\InvoiceService;
use App\Services\PayPal\TokenManager;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
                self::cancelOrderAction(),

                Action::make('send_paypal_invoice')
                    ->label('Send PayPal Invoice')
                    ->icon(Heroicon::OutlinedCreditCard)
                    ->color('warning')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalHeading('Send PayPal Invoice')
                    ->modalDescription('This will create and send a PayPal invoice to the customer for payment.')
                    ->action(function (Order $record) {
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
                        fn (Order $record) => $record->payment_status === PaymentStatus::Unpaid &&
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
                    AuthorizedDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders will appear here as customers place them.');
    }

    /**
     * Cancel action — distinct from the generic statusTransitionAction because
     * cancellation needs a reason (audit trail) and may trigger a Stripe refund
     * for paid orders. Reason is collected from the modal, passed to
     * TransitionOrderStatus's downstream events via the Refund row, and
     * referenced in the success notification.
     */
    private static function cancelOrderAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel Order')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->authorize('update')
            ->requiresConfirmation()
            ->modalHeading('Cancel Order')
            ->modalDescription(fn (Order $record): string => $record->payment_status === PaymentStatus::Paid && $record->stripe_payment_intent_id
                ? 'This will cancel the order, restock any deducted ingredients, and refund the full amount to the customer\'s card via Stripe.'
                : 'This will cancel the order and reverse any coupon/gift-card use. The customer will not be charged.')
            ->modalSubmitActionLabel('Cancel Order')
            ->schema([
                Textarea::make('reason')
                    ->label('Cancellation reason')
                    ->placeholder('e.g. customer requested, ingredient unavailable, store closed')
                    ->rows(3)
                    ->maxLength(500),
            ])
            ->action(function (Order $record, array $data): void {
                resolve(TransitionOrderStatus::class)($record, OrderStatus::Cancelled);

                $refund = resolve(RefundStripePayment::class)(
                    $record->refresh(),
                    initiatedBy: Auth::user() instanceof User ? Auth::user() : null,
                    reason: $data['reason'] ?? null,
                );

                Notification::make()
                    ->title($refund ? 'Order cancelled and refunded' : 'Order cancelled')
                    ->body($refund ? "Refunded {$refund->amount->formatted()} to the customer." : null)
                    ->color($refund ? 'success' : 'warning')
                    ->send();
            })
            ->visible(fn (Order $record): bool => in_array(OrderStatus::Cancelled, TransitionOrderStatus::allowedTransitions($record)));
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
