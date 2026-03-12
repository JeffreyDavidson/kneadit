<?php

namespace App\Mail;

use App\Models\CateringInquiry;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Mail\Concerns\BakerBranded;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CateringQuote extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    use BakerBranded;

    public function __construct(
        public CateringInquiry $inquiry
    ) {}

    public function envelope(): Envelope
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Your Catering Quote — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.catering-quote',
            with: [
                'inquiry' => $this->inquiry,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
