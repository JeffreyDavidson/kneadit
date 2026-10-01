<?php

namespace App\Mail\Concerns;

use Illuminate\Mail\Mailables\Headers;

/**
 * Adds the RFC 8058 one-click unsubscribe headers to a MarketingMail.
 * The visible footer link is rendered by the shared email layout from the
 * `unsubscribeUrl` view variable that BaseMailable supplies.
 */
trait SendsMarketingMail
{
    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => "<{$this->unsubscribeUrl()}>",
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }
}
