<?php

namespace App\Actions\Stripe;

use App\Actions\Customers\ApplyCateringDepositPayment;
use App\Models\Customers\CateringInquiry;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;

class HandleConnectCheckoutCompleted
{
    public function __construct(
        private readonly TenancyManager $tenancyManager,
        private readonly HandleCheckoutComplete $handleCheckoutComplete,
        private readonly ApplyCateringDepositPayment $applyCateringDepositPayment,
    ) {}

    public function __invoke(mixed $session): void
    {
        $sessionId = data_get($session, 'id');
        $metadata = data_get($session, 'metadata');
        $orderId = data_get($metadata, 'order_id');
        $cateringInquiryId = data_get($metadata, 'catering_inquiry_id');
        $tenantId = data_get($metadata, 'tenant_id');

        if (! is_string($sessionId) || $sessionId === '') {
            return;
        }

        if (data_get($session, 'payment_status') !== 'paid') {
            Log::info('Ignoring unpaid Stripe Connect checkout session', ['session_id' => $sessionId]);

            return;
        }

        $paymentIntent = data_get($session, 'payment_intent');
        $paymentIntentId = is_object($paymentIntent) ? data_get($paymentIntent, 'id') : $paymentIntent;
        $paymentIntentId = is_string($paymentIntentId) && $paymentIntentId !== '' ? $paymentIntentId : null;

        Log::info('Stripe Connect checkout completed', [
            'session_id' => $sessionId,
            'order_id' => $orderId,
            'tenant_id' => $tenantId,
        ]);

        if (! $tenantId) {
            Log::warning('Stripe Connect checkout.session.completed missing tenant_id', ['session_id' => $sessionId]);

            return;
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::query()->find($tenantId);
        if (! $tenant) {
            Log::warning('Tenant not found for checkout session', ['tenant_id' => $tenantId]);

            return;
        }

        try {
            $this->tenancyManager->withinTenant($tenant, function () use ($sessionId, $orderId, $cateringInquiryId, $paymentIntentId, $session): void {
                if ($cateringInquiryId) {
                    $inquiry = CateringInquiry::query()->whereKey($cateringInquiryId)->first();

                    if (! $inquiry) {
                        Log::warning('No catering inquiry for checkout session', ['session_id' => $sessionId]);

                        return;
                    }

                    $amountTotal = data_get($session, 'amount_total', 0);

                    ($this->applyCateringDepositPayment)(
                        $inquiry,
                        $sessionId,
                        $paymentIntentId,
                        is_numeric($amountTotal) ? ((int) $amountTotal) / 100 : 0,
                    );

                    return;
                }

                $orderQuery = Order::query()->where('stripe_checkout_session_id', $sessionId);

                if (is_int($orderId) || (is_string($orderId) && ctype_digit($orderId))) {
                    $orderQuery->whereKey((int) $orderId);
                }

                $order = $orderQuery->first();
                if ($order) {
                    $amountTotal = data_get($session, 'amount_total');

                    ($this->handleCheckoutComplete)(
                        $order,
                        $paymentIntentId,
                        is_numeric($amountTotal) ? (int) $amountTotal : -1,
                    );
                }
            });
        } catch (\Exception $e) {
            Log::warning('Error processing checkout session for tenant', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
