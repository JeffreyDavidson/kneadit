<?php

namespace App\Mail\Platform;

use App\Mail\BaseMailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class UnapprovedFreeForeverAlertMail extends BaseMailable
{
    /**
     * @param array<int, array{id: string, name: string, email: string}> $unapproved
     */
    public function __construct(
        public array $unapproved,
    ) {}

    public function envelope(): Envelope
    {
        $count = count($this->unapproved);

        return new Envelope(
            subject: "🚨 KneadIt: {$count} tenant(s) marked free_forever without an approved grant",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform.unapproved-free-forever-alert',
            with: [
                'unapproved' => $this->unapproved,
            ],
        );
    }
}
