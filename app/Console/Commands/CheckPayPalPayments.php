<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\PayPalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPayPalPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:check-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check PayPal invoice payment statuses and update orders accordingly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $paypalService = app(PayPalService::class);
        
        // Get all unpaid orders with PayPal invoice IDs
        $orders = Order::where('payment_status', 'unpaid')
            ->whereNotNull('paypal_invoice_id')
            ->get();
            
        $updatedCount = 0;
        
        $this->info("Checking {$orders->count()} orders with PayPal invoices...");
        
        foreach ($orders as $order) {
            $this->line("Checking order #{$order->order_number} (Invoice: {$order->paypal_invoice_id})");
            
            $status = $paypalService->getInvoiceStatus($order->paypal_invoice_id);
            
            if ($status) {
                switch ($status) {
                    case 'PAID':
                        $order->update(['payment_status' => 'paid']);
                        $this->info("✓ Order #{$order->order_number} marked as paid");
                        $updatedCount++;
                        
                        Log::info('PayPal payment received', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                            'paypal_invoice_id' => $order->paypal_invoice_id
                        ]);
                        break;
                        
                    case 'CANCELLED':
                        $order->update(['payment_status' => 'cancelled']);
                        $this->warning("⚠ Order #{$order->order_number} PayPal invoice cancelled");
                        $updatedCount++;
                        break;
                        
                    case 'REFUNDED':
                        $order->update(['payment_status' => 'refunded']);
                        $this->warning("⚠ Order #{$order->order_number} PayPal payment refunded");
                        $updatedCount++;
                        break;
                        
                    case 'SENT':
                    case 'SCHEDULED':
                    case 'DRAFT':
                        // These are still pending, no action needed
                        $this->line("- Order #{$order->order_number} still pending (Status: {$status})");
                        break;
                        
                    default:
                        $this->warning("? Unknown PayPal status '{$status}' for order #{$order->order_number}");
                        Log::warning('Unknown PayPal invoice status', [
                            'order_id' => $order->id,
                            'status' => $status,
                            'paypal_invoice_id' => $order->paypal_invoice_id
                        ]);
                }
            } else {
                $this->error("✗ Failed to check status for order #{$order->order_number}");
            }
        }
        
        $this->info("Completed! Updated {$updatedCount} out of {$orders->count()} orders.");
        
        return Command::SUCCESS;
    }
}
