<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;
use App\Models\Staff\User;
use App\ValueObjects\Money;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Applies a paid Stripe Checkout deposit to its catering inquiry.
 *
 * Both completion paths (the success redirect and the Connect webhook) call this, so a
 * payment is recorded once whichever arrives first. A payment that can't be recorded,
 * because the deposit is already in or the inquiry no longer takes deposits, is left
 * as is: it is logged and the owners are notified so they can refund it in Stripe.
 */
class ApplyCateringDepositPayment
{
    public function __construct(private readonly RecordCateringDeposit $recordCateringDeposit) {}

    public function __invoke(
        CateringInquiry $inquiry,
        string $sessionId,
        ?string $paymentIntentId,
        float $amountDollars,
    ): CateringInquiry {
        return DB::transaction(function () use ($inquiry, $sessionId, $paymentIntentId, $amountDollars): CateringInquiry {
            $current = CateringInquiry::query()
                ->whereKey($inquiry->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $alreadyRecorded = $current->deposit_paid_at !== null
                && $paymentIntentId !== null
                && $current->stripe_payment_intent_id === $paymentIntentId;

            if ($alreadyRecorded) {
                return $current;
            }

            if ($current->deposit_paid_at !== null || ! $current->status->acceptsDeposit()) {
                $this->report($current, $sessionId, $paymentIntentId, $amountDollars);

                return $current;
            }

            $current->forceFill(['stripe_payment_intent_id' => $paymentIntentId])->save();

            return ($this->recordCateringDeposit)($current, $amountDollars, $paymentIntentId);
        });
    }

    private function report(CateringInquiry $inquiry, string $sessionId, ?string $paymentIntentId, float $amountDollars): void
    {
        $firstReport = Cache::add("catering-deposit-unapplied:{$sessionId}", true, now()->addDays(7));

        if (! $firstReport) {
            return;
        }

        $depositRecorded = $inquiry->deposit_paid_at !== null;

        Log::warning('Catering deposit payment could not be applied to the inquiry', [
            'inquiry' => $inquiry->id,
            'session_id' => $sessionId,
            'payment_intent' => $paymentIntentId,
            'status' => $inquiry->status->value,
            'deposit_recorded' => $depositRecorded,
        ]);

        $paid = Money::fromDollars($amountDollars)->formatted();
        $reason = $depositRecorded
            ? 'a deposit was already recorded for this inquiry'
            : "the inquiry is now marked {$inquiry->status->getLabel()}";

        Notification::make()
            ->title("Catering deposit payment needs review for {$inquiry->customer_name}")
            ->body("{$inquiry->customer_name} paid {$paid} by card, but {$reason}. The payment was not recorded, so refund it in Stripe or update the inquiry if it should count.")
            ->warning()
            ->sendToDatabase(User::query()->owners()->get());
    }
}
