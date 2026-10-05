<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PlatformCampaignMail extends PlatformMailable
{
    public function __construct(
        public string $campaignSubject,
        public string $campaignBody,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaignSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform.campaign',
            with: [
                'body' => $this->campaignBody,
                'emailSubject' => $this->campaignSubject,
            ],
        );
    }
}
