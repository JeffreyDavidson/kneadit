<?php

namespace App\Console\Commands\PayPal;

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\PayPal\PaymentVerifier;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('paypal:check-payments')]
#[Description('Check PayPal invoice payment statuses and update orders across all tenants')]
class CheckPayPalPaymentsCommand extends Command
{
    public function handle(TenancyManager $tenancyManager): int
    {
        // Skip entirely if PayPal isn't configured at the platform level
        if (! config('services.paypal.client_id')) {
            return Command::SUCCESS;
        }

        $tenants = Tenant::query()->cursor();

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () use ($tenant) {
                    // Skip tenants without PayPal configured
                    $clientId = settings('paypal_client_id');
                    if (! $clientId) {
                        return;
                    }

                    $this->processTenant($tenant);
                });
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
                Log::error("PayPal check failed for tenant {$tenant->id}", ['error' => $e->getMessage()]);
            }
        }

        return Command::SUCCESS;
    }

    protected function processTenant(Tenant $tenant): void
    {
        $paymentVerifier = resolve(PaymentVerifier::class);

        $orders = Order::query()->where('payment_status', PaymentStatus::Unpaid)
            ->whereNotNull('paypal_invoice_id')
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $this->info("Tenant {$tenant->store_name}: checking {$orders->count()} orders...");

        foreach ($orders as $order) {
            if (! $order->paypal_invoice_id) {
                continue;
            }

            $status = $paymentVerifier->getInvoiceStatus($order->paypal_invoice_id);

            if (! $status) {
                $this->error("  ✗ Failed to check order #{$order->order_number}");

                continue;
            }

            match ($status) {
                'PAID' => tap($order, function (Order $o) {
                    app(MarkOrderPaid::class)($o);
                    $this->info("  ✓ #{$o->order_number} paid");
                }),
                'CANCELLED' => tap($order, function (Order $o) {
                    $o->update(['payment_status' => PaymentStatus::Cancelled]);
                    $this->warn("  ⚠ #{$o->order_number} cancelled");
                }),
                'REFUNDED' => tap($order, function (Order $o) {
                    $o->update(['payment_status' => PaymentStatus::Refunded]);
                    $this->warn("  ⚠ #{$o->order_number} refunded");
                }),
                default => null,
            };
        }
    }
}
