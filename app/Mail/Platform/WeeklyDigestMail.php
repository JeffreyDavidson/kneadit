<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMail;
use App\Models\Orders\OrderItem;
use App\ValueObjects\Money;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class WeeklyDigestMail extends PlatformMail
{
    /**
     * @param  array{total_orders: int, total_revenue: Money, new_customers: int, avg_order_value: Money}  $stats
     * @param  Collection<int, OrderItem>  $topProducts
     * @param  Collection<int, array{name: string, days_since_last_order: ?int}>  $atRiskCustomers
     */
    public function __construct(
        public array $stats,
        public Collection $topProducts,
        public Collection $atRiskCustomers,
        public int $upcomingCount,
        public string $storeName,
        public string $adminUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "📊 Weekly Digest — {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform.weekly-digest',
            with: [
                'stats' => $this->stats,
                'topProducts' => $this->topProducts,
                'atRiskCustomers' => $this->atRiskCustomers,
                'upcomingCount' => $this->upcomingCount,
                'storeName' => $this->storeName,
                'adminUrl' => $this->adminUrl,
            ],
        );
    }
}
