<?php

namespace App\Actions\Stripe;

use App\Actions\Orders\MarkOrderPaid;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenancyManager;
use Illuminate\Support\Facades\Log;

class HandleConnectCheckoutCompleted
{
    public function __construct(
        private TenancyManager $tenancyManager,
        private MarkOrderPaid $markOrderPaid,
    ) {}

    public function __invoke(mixed $session): void
    {
        $sessionId = data_get($session, 'id');
        $metadata = data_get($session, 'metadata');
        $orderId = data_get($metadata, 'order_id');
        $tenantId = data_get($metadata, 'tenant_id');

        if (! $sessionId) {
            return;
        }

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
            $this->tenancyManager->withinTenant($tenant, function () use ($sessionId, $tenant) {
                $order = Order::query()->where('stripe_checkout_session_id', $sessionId)->first();
                if ($order) {
                    ($this->markOrderPaid)($order);
                    Log::info('Order marked paid via webhook', [
                        'order' => $order->order_number,
                        'tenant' => $tenant->id,
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::warning('Error processing checkout session for tenant', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
