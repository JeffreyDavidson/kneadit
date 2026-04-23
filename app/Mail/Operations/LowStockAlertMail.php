<?php

namespace App\Mail\Operations;

use App\Mail\BaseMailable;
use App\Mail\Concerns\BakerBranded;
use App\Models\Inventory\Ingredient;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class LowStockAlertMail extends BaseMailable
{
    use BakerBranded;

    /**
     * @param Collection<int, Ingredient> $ingredients
     */
    public function __construct(
        public Collection $ingredients,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->bakerFrom(),
            subject: 'Low-stock ingredients — daily alert',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.operations.low-stock-alert',
            with: [
                'ingredients' => $this->ingredients,
            ],
        );
    }
}
