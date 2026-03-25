<?php

namespace App\Mail;

use App\Mail\Concerns\BakerBranded;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HappyBirthday extends Mailable implements ShouldQueue
{
    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    use BakerBranded;
    use Queueable, SerializesModels;

    public string $storeName;

    public function __construct(
        public Customer $customer,
        public ?Coupon $coupon = null
    ) {
        $this->storeName = Setting::get('store_name', 'Our Bakery');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "🎂 Happy Birthday, {$this->customer->name}! A Sweet Gift From {$this->storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.happy-birthday',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
