<?php

namespace App\Mail\Concerns;

use App\Models\Setting;
use Illuminate\Mail\Mailables\Address;

trait BakerBranded
{
    /**
     * Get the sender address branded with the baker's store name.
     * Sends from platform domain (for deliverability) but with baker's name.
     */
    protected function bakerFrom(): Address
    {
        $storeName = Setting::get('store_name', 'KneadIt Bakery');

        return new Address(
            address: config('mail.from.address', 'hello@getkneadit.app'),
            name: "{$storeName} via KneadIt",
        );
    }

    /**
     * Get the baker's email as reply-to so customers reply to the baker directly.
     */
    protected function bakerReplyTo(): ?Address
    {
        $email = Setting::get('store_email');
        $storeName = Setting::get('store_name', 'KneadIt Bakery');

        if (! $email) {
            return null;
        }

        return new Address($email, $storeName);
    }
}
