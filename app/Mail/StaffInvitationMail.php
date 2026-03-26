<?php

namespace App\Mail;

use App\Models\StaffInvitation;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class StaffInvitationMail extends BaseMailable
{
    public function __construct(
        public StaffInvitation $invitation,
        public string $storeName,
        public string $acceptUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->storeName} on KneadIt",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-invitation',
            with: [
                'invitation' => $this->invitation,
                'storeName' => $this->storeName,
                'acceptUrl' => $this->acceptUrl,
                'role' => ucfirst($this->invitation->role),
                'expiresAt' => $this->invitation->expires_at?->format('F j, Y'),
            ],
        );
    }
}
