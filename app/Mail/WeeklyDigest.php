<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class WeeklyDigest extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    use BakerBranded;
    use Queueable, SerializesModels;

    public array $stats;

    public Collection $topProducts;

    public Collection $atRiskCustomers;

    public int $upcomingCount;

    public string $storeName;

    public string $adminUrl;

    public function __construct()
    {
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();
        $nextWeekStart = now()->startOfWeek();
        $nextWeekEnd = now()->endOfWeek();

        $weekOrders = Order::query()->whereBetween('created_at', [$weekStart, $weekEnd]);

        $totalOrders = (clone $weekOrders)->count();
        $totalRevenue = (clone $weekOrders)->sum('total');
        $newCustomers = Customer::query()->whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $this->stats = [
            'total_orders' => $totalOrders,
            'total_revenue' => number_format((float) $totalRevenue, 2),
            'new_customers' => $newCustomers,
            'avg_order_value' => number_format($avgOrderValue, 2),
        ];

        $this->topProducts = OrderItem::query()->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', fn (Builder $q) => $q->whereBetween('created_at', [$weekStart, $weekEnd]))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get();

        $this->atRiskCustomers = Customer::query()->whereHas('orders')
            ->whereDoesntHave('orders', fn (Builder $q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->limit(5)
            ->get();

        $this->upcomingCount = Order::query()->whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
            ->where('status', '!=', OrderStatus::Cancelled)
            ->count();

        $this->storeName = Setting::get('store_name', 'KneadIt Bakery');
        $this->adminUrl = url('/admin');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "📊 Weekly Digest — {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.weekly-digest',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
