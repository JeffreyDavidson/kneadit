<?php

declare(strict_types=1);

namespace App\Mail\Concerns;

/**
 * Marks a mailable as marketing: a message the customer did not specifically
 * ask for. Marketing mails must carry an unsubscribe link and the one-click
 * List-Unsubscribe headers (see SendsMarketingMail), and their senders must
 * skip customers who have opted out.
 *
 * Transactional mail (order updates, tracking links, quotes, rewards) never
 * implements this and is never suppressed.
 */
interface MarketingMail
{
    public function unsubscribeUrl(): string;
}
