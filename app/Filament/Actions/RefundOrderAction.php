<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Actions\Orders\RefundStripePayment;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Exceptions\Orders\OrderRefundInProgressException;
use App\Exceptions\Stripe\StripeRefundFailedException;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Refunds an order that was cancelled but is still marked Paid, such as one
 * cancelled before cancelling refunded automatically. Only offered for
 * Stripe payments, and `authorize('refund')` keeps it to managers and above.
 */
class RefundOrderAction extends Action
{
    #[\Override]
    public static function getDefaultName(): ?string
    {
        return 'refund';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Refund');
        $this->icon(Heroicon::OutlinedReceiptRefund);
        $this->color('warning');
        $this->authorize('refund');
        $this->requiresConfirmation();
        $this->modalHeading('Refund Order');
        $this->modalDescription('This refunds the full amount to the customer\'s card via Stripe.');
        $this->modalSubmitActionLabel('Refund');
        $this->schema([
            Textarea::make('reason')
                ->label('Refund reason')
                ->rows(3)
                ->maxLength(500),
        ]);
        $this->action(function (Order $record, array $data): void {
            $reason = $data['reason'] ?? null;
            $user = auth()->user();

            try {
                $refund = resolve(RefundStripePayment::class)(
                    $record,
                    $user instanceof User ? $user : null,
                    is_string($reason) && $reason !== '' ? $reason : null,
                );
            } catch (OrderRefundInProgressException $exception) {
                Notification::make()->title($exception->getMessage())->warning()->send();

                return;
            } catch (StripeRefundFailedException $exception) {
                Notification::make()
                    ->title('Stripe could not refund this order')
                    ->body($exception->getPrevious()?->getMessage())
                    ->danger()
                    ->send();

                return;
            }

            if (! $refund) {
                Notification::make()
                    ->title('Nothing to refund')
                    ->body('This order was already refunded, or has no Stripe payment to refund.')
                    ->warning()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Order refunded')
                ->body("Refunded {$refund->amount->formatted()} to the customer.")
                ->success()
                ->send();
        });
        $this->visible(fn (Order $record): bool => $record->status === OrderStatus::Cancelled
            && $record->payment_status === PaymentStatus::Paid
            && (bool) $record->stripe_payment_intent_id);
    }
}
