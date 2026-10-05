<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Actions\Orders\CancelOrder;
use App\Actions\Orders\TransitionOrderStatus;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\InvalidOrderTransitionException;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Cancels an order from the orders table and from the order page, so both go
 * through CancelOrder. A paid Stripe order is refunded first and left alone if
 * Stripe refuses. `authorize('cancel')` keeps paid orders to managers and above.
 */
class CancelOrderAction extends Action
{
    #[\Override]
    public static function getDefaultName(): ?string
    {
        return 'cancel';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Cancel Order');
        $this->icon(Heroicon::OutlinedXCircle);
        $this->color('danger');
        $this->authorize('cancel');
        $this->requiresConfirmation();
        $this->modalHeading('Cancel Order');
        $this->modalDescription(fn (Order $record): string => match (true) {
            $record->payment_status === PaymentStatus::Paid && (bool) $record->stripe_payment_intent_id => 'This will cancel the order, restock any deducted ingredients, and refund the full amount to the customer\'s card via Stripe.',
            $record->payment_status->holdsPayment() => 'This will cancel the order and reverse any coupon/gift-card use. Payment was not taken through Stripe, so the customer must be refunded outside the app.',
            default => 'This will cancel the order and reverse any coupon/gift-card use. The customer will not be charged.',
        });
        $this->modalSubmitActionLabel('Cancel Order');
        $this->schema([
            Textarea::make('reason')
                ->label('Cancellation reason')
                ->placeholder('e.g. customer requested, ingredient unavailable, store closed')
                ->rows(3)
                ->maxLength(500),
        ]);
        $this->action(function (Order $record, array $data): void {
            $reason = $data['reason'] ?? null;
            $user = auth()->user();

            try {
                $refund = resolve(CancelOrder::class)(
                    $record,
                    $user instanceof User ? $user : null,
                    is_string($reason) && $reason !== '' ? $reason : null,
                );
            } catch (StripeRefundFailedException $exception) {
                Notification::make()
                    ->title('Stripe could not refund this order, so it was not cancelled')
                    ->body($exception->getPrevious()?->getMessage())
                    ->danger()
                    ->send();

                return;
            } catch (InvalidOrderTransitionException $exception) {
                Notification::make()->title($exception->getMessage())->danger()->send();

                return;
            }

            if ($refund) {
                Notification::make()
                    ->title('Order cancelled and refunded')
                    ->body("Refunded {$refund->amount->formatted()} to the customer.")
                    ->success()
                    ->send();

                return;
            }

            Notification::make()
                ->title($record->payment_status->holdsPayment() ? 'Order cancelled. Refund the customer outside Stripe' : 'Order cancelled')
                ->warning()
                ->send();
        });
        $this->visible(fn (Order $record): bool => in_array(OrderStatus::Cancelled, TransitionOrderStatus::allowedTransitions($record), true));
    }
}
