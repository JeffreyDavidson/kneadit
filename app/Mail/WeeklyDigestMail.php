<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Services\Reporting\WeeklyDigestDataCollector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WeeklyDigestMail extends BaseMailable
{
    use BakerBranded;

    /** @var array<string, mixed> */
    public array $stats;

    /** @var Collection<int, OrderItem> */
    public Collection $topProducts;

    /** @var Collection<int, Customer> */
    public Collection $atRiskCustomers;

    public int $upcomingCount;

    public string $storeName;

    public string $adminUrl;

    public function __construct()
    {
        $data = resolve(WeeklyDigestDataCollector::class)->collect();

        $this->stats = $data['stats'];
        $this->topProducts = $data['topProducts'];
        $this->atRiskCustomers = $data['atRiskCustomers'];
        $this->upcomingCount = $data['upcomingCount'];
        $this->storeName = $data['storeName'];
        $this->adminUrl = $data['adminUrl'];
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

    /** @return array<int, Attachment> */
    public function attachments(): array
    {
        return [];
    }
}
