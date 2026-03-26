<?php

namespace App\Console\Commands;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\PayPalService;
use App\Services\Tenant\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPayPalPayments extends Command
{
    protected $signature = 'paypal:check-payments';

    protected $description = 'Check PayPal invoice payment statuses and update orders across all tenants';

    public function handle(TenancyManager $tenancyManager): int
    {
        // Skip entirely if PayPal isn't configured at the platform level
        if (! config('services.paypal.client_id')) {
            return Command::SUCCESS;
        }

        $tenants = Tenant::cursor();

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
        $paypalService = resolve(PayPalService::class);

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

            $status = $paypalService->getInvoiceStatus($order->paypal_invoice_id);

            if (! $status) {
                $this->error("  ✗ Failed to check order #{$order->order_number}");

                continue;
            }

            match ($status) {
                'PAID' => tap($order, function (Order $o) {
                    $o->update(['payment_status' => PaymentStatus::Paid]);
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
