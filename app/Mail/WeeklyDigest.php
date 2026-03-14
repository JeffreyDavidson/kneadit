<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Mail\Concerns\BakerBranded;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class WeeklyDigest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    use BakerBranded;

    public array $stats;

    public $topProducts;

    public $atRiskCustomers;

    public int $upcomingCount;

    public string $storeName;

    public string $adminUrl;

    public function __construct()
    {
        $weekStart = now()->subWeek()->startOfWeek();
        $weekEnd = now()->subWeek()->endOfWeek();
        $nextWeekStart = now()->startOfWeek();
        $nextWeekEnd = now()->endOfWeek();

        $weekOrders = Order::whereBetween('created_at', [$weekStart, $weekEnd]);

        $totalOrders = (clone $weekOrders)->count();
        $totalRevenue = (clone $weekOrders)->sum('total');
        $newCustomers = Customer::whereBetween('created_at', [$weekStart, $weekEnd])->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $this->stats = [
            'total_orders' => $totalOrders,
            'total_revenue' => number_format((float) $totalRevenue, 2),
            'new_customers' => $newCustomers,
            'avg_order_value' => number_format($avgOrderValue, 2),
        ];

        $this->topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [$weekStart, $weekEnd]))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->with('product')
            ->get();

        $this->atRiskCustomers = Customer::whereHas('orders')
            ->whereDoesntHave('orders', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->limit(5)
            ->get();

        $this->upcomingCount = Order::whereBetween('delivery_date', [$nextWeekStart, $nextWeekEnd])
            ->where('status', '!=', 'cancelled')
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
