<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\PayPalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPayPalPayments extends Command
{
    protected $signature = 'paypal:check-payments';

    protected $description = 'Check PayPal invoice payment statuses and update orders across all tenants';

    public function handle()
    {
        // Skip entirely if PayPal isn't configured at the platform level
        if (! config('services.paypal.client_id')) {
            return Command::SUCCESS;
        }

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);
            Setting::flushCache();

            try {
                // Skip tenants without PayPal configured
                $clientId = Setting::get('paypal_client_id');
                if (! $clientId) {
                    continue;
                }

                $this->processTenant($tenant);
            } catch (\Exception $e) {
                $this->error("Error processing {$tenant->id}: {$e->getMessage()}");
                Log::error("PayPal check failed for tenant {$tenant->id}", ['error' => $e->getMessage()]);
            }
        }

        return Command::SUCCESS;
    }

    protected function processTenant(Tenant $tenant): void
    {
        $paypalService = app(PayPalService::class);

        $orders = Order::where('payment_status', 'unpaid')
            ->whereNotNull('paypal_invoice_id')
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        $this->info("Tenant {$tenant->store_name}: checking {$orders->count()} orders...");

        foreach ($orders as $order) {
            $status = $paypalService->getInvoiceStatus($order->paypal_invoice_id);

            if (! $status) {
                $this->error("  ✗ Failed to check order #{$order->order_number}");
                continue;
            }

            match ($status) {
                'PAID' => tap($order, fn ($o) => $o->update(['payment_status' => 'paid']))
                    && $this->info("  ✓ #{$order->order_number} paid"),
                'CANCELLED' => tap($order, fn ($o) => $o->update(['payment_status' => 'cancelled']))
                    && $this->warn("  ⚠ #{$order->order_number} cancelled"),
                'REFUNDED' => tap($order, fn ($o) => $o->update(['payment_status' => 'refunded']))
                    && $this->warn("  ⚠ #{$order->order_number} refunded"),
                default => null,
            };
        }
    }
}
