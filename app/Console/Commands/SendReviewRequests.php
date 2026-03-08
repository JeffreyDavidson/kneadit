<?php

namespace App\Console\Commands;

use App\Mail\ReviewRequest;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReviewRequests extends Command
{
    protected $signature = 'reviews:send-requests';

    protected $description = 'Send review request emails for recently delivered orders';

    public function handle(): int
    {
        $tenants = \App\Models\Tenant::all();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            try {
                if (Setting::get('review_requests_enabled', '1') !== '1') {
                    continue;
                }

                $delayHours = (int) Setting::get('review_request_delay_hours', '24');

                $orders = Order::where('status', 'delivered')
                    ->whereNull('review_request_sent_at')
                    ->where('updated_at', '<=', now()->subHours($delayHours))
                    ->whereHas('customer', fn ($q) => $q->whereNotNull('email'))
                    ->with('customer')
                    ->get();

                foreach ($orders as $order) {
                    Mail::to($order->customer->email)->send(new ReviewRequest($order));

                    $order->update(['review_request_sent_at' => now()]);

                    $this->info("Sent review request for order #{$order->order_number}");
                }
            } catch (\Throwable $e) {
                $this->error("Failed for {$tenant->id}: {$e->getMessage()}");
            }

            tenancy()->end();
        }

        return self::SUCCESS;
    }
}
