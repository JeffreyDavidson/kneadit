<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Coupon;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HappyBirthday extends Mailable implements ShouldQueue
{
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
