<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Mail\ReviewRequest;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\Tenant\TenancyManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewRequests extends Command
{
    protected $signature = 'reviews:send-requests';

    protected $description = 'Send review request emails for recently delivered orders';

    public function handle(TenancyManager $tenancyManager): int
    {
        $tenants = Tenant::cursor();
        $failures = 0;

        foreach ($tenants as $tenant) {
            try {
                $tenancyManager->withinTenant($tenant, function () {
                    if (settings('review_requests_enabled', '1') !== '1') {
                        return;
                    }

                    $delayHours = (int) settings('review_request_delay_hours', '24');

                    $orders = Order::query()->where('status', OrderStatus::Delivered)
                        ->whereNull('review_request_sent_at')
                        ->where('updated_at', '<=', now()->subHours($delayHours))
                        ->whereHas('customer', fn (Builder $q) => $q->whereNotNull('email'))
                        ->with('customer')
                        ->get();

                    foreach ($orders as $order) {
                        Mail::to($order->customer?->email)->send(new ReviewRequest($order));

                        $order->update(['review_request_sent_at' => now()]);

                        $this->info("Sent review request for order #{$order->order_number}");
                    }
                });
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->id}: {$e->getMessage()}");
                Log::warning('Review request processing failed', ['tenant' => $tenant->id, 'error' => $e->getMessage()]);
                $failures++;
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
