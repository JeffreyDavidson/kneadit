<?php

namespace App\Mail\Marketing;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Customers\CateringInquiry;
use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CateringQuoteMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public CateringInquiry $inquiry,
    ) {
        $this->inquiry->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        $storeName = resolve(TenantSettings::class)->store->name;

        return new Envelope(
            from: $this->bakerFrom(),
            replyTo: array_filter([$this->bakerReplyTo()]),
            subject: "Your Catering Quote — {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            html: 'emails.marketing.catering-quote',
            with: [
                'inquiry' => $this->inquiry,
            ],
        );
    }
}
