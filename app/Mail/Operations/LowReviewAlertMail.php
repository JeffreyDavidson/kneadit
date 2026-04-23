<?php

namespace App\Mail\Operations;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Engagement\Review;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LowReviewAlertMail extends BaseMailable
{
    use BakerBranded;

    public function __construct(
        public Review $review,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            subject: "⚠️ {$this->review->rating}-star review from {$this->review->customer_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.operations.low-review-alert',
            with: [
                'review' => $this->review,
            ],
        );
    }
}
