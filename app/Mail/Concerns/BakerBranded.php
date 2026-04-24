<?php

namespace App\Mail\Concerns;

use App\Services\Settings\TenantSettings;
use Illuminate\Mail\Mailables\Address;

trait BakerBranded
{
    /**
     * Get the sender address branded with the baker's store name.
     * Sends from platform domain (for deliverability) but with baker's name.
     */
    protected function bakerFrom(): Address
    {
        $store = resolve(TenantSettings::class)->store;

        return new Address(
            address: config('mail.from.address', 'hello@getkneadit.app'),
            name: "{$store->name} via KneadIt",
        );
    }

    /**
     * Get the baker's email as reply-to so customers reply to the baker directly.
     */
    protected function bakerReplyTo(): ?Address
    {
        $store = resolve(TenantSettings::class)->store;

        if (! $store->email) {
            return null;
        }

        return new Address($store->email, $store->name);
    }
}
