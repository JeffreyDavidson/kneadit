<?php

declare(strict_types=1);

namespace App\Mail\Platform;

use App\Mail\PlatformMail;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SubscriberWithoutBakeryAlertMail extends PlatformMail
{
    /** @param  array<int, int>  $userIds */
    public function __construct(
        public array $userIds,
    ) {}

    public function envelope(): Envelope
    {
        $count = count($this->userIds);

        return new Envelope(
            subject: "KneadIt: {$count} paying account(s) without a bakery",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform.subscriber-without-bakery-alert',
            text: 'emails.platform.subscriber-without-bakery-alert-text',
            with: [
                'userIds' => $this->userIds,
            ],
        );
    }
}
