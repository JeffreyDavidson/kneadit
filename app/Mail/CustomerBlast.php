<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use App\Mail\Concerns\BakerBranded;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerBlast extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    use BakerBranded;

    public function __construct(
        public string $campaignSubject,
        public string $campaignBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: $this->campaignSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-blast',
            with: [
                'body' => $this->campaignBody,
                'storeName' => Setting::get('store_name', 'Our Bakery'),
            ],
        );
    }
}
