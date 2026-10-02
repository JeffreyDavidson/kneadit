<?php

namespace App\Console\Commands\PayPal;

use App\Actions\Orders\MarkOrderPaid;
use App\Enums\Orders\PaymentStatus;
use App\Models\Orders\Order;
use App\Models\Platform\Tenant;
use App\Services\PayPal\PaymentVerifier;
use App\Services\Settings\SettingsManager;
use App\Services\Tenants\TenancyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

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

        $failures = $tenancyManager->forEachTenant(
            function (Tenant $tenant): void {
                // Skip tenants without PayPal configured
                if (! resolve(SettingsManager::class)->get('paypal_client_id')) {
                    return;
                }

                // Built per tenant: the verifier reads that tenant's PayPal credentials when it is created,
                // and marking an order paid reads the tenant's settings.
                $this->processTenant($tenant, resolve(PaymentVerifier::class), resolve(MarkOrderPaid::class));
            },
            function (Tenant $tenant, Throwable $e): void {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
                Log::error("PayPal check failed for tenant {$tenant->id}", ['error' => $e->getMessage()]);
            },
        );

        return $failures > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function processTenant(Tenant $tenant, PaymentVerifier $paymentVerifier, MarkOrderPaid $markOrderPaid): void
    {
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
                'PAID' => tap($order, function (Order $o) use ($markOrderPaid): void {
                    $markOrderPaid($o);
                    $this->info("  ✓ #{$o->order_number} paid");
                }),
                'CANCELLED' => tap($order, function (Order $o): void {
                    $o->update(['payment_status' => PaymentStatus::Cancelled]);
                    $this->warn("  ⚠ #{$o->order_number} cancelled");
                }),
                'REFUNDED' => tap($order, function (Order $o): void {
                    $o->update(['payment_status' => PaymentStatus::Refunded]);
                    $this->warn("  ⚠ #{$o->order_number} refunded");
                }),
                default => null,
            };
        }
    }
}
