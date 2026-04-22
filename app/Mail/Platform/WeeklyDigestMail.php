<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Orders\OrderItem;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class WeeklyDigestMail extends BaseMailable
{
    use BakerBranded;

    /**
     * @param array<string, mixed> $stats
     * @param Collection<int, OrderItem> $topProducts
     * @param Collection<int, array{name: string, days_since_last_order: ?int}> $atRiskCustomers
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
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "📊 Weekly Digest — {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.platform.weekly-digest',
        );
    }
}
