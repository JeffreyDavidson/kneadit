<?php

namespace App\Actions\Stripe;

use App\Actions\Orders\MarkOrderPaid;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Log;

class HandleConnectCheckoutCompleted
{
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
            tenancy()->initialize($tenant);

            $order = Order::query()->where('stripe_checkout_session_id', $sessionId)->first();
            if ($order) {
                app(MarkOrderPaid::class)($order);
                Log::info('Order marked paid via webhook', [
                    'order' => $order->order_number,
                    'tenant' => $tenant->id,
                ]);
            }

            tenancy()->end();
        } catch (\Exception $e) {
            tenancy()->end();
            Log::warning('Error processing checkout session for tenant', [
                'tenant' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
