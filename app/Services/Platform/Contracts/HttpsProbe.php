<?php

declare(strict_types=1);

namespace App\Services\Platform\Contracts;

interface HttpsProbe
{
    /**
     * Whether the domain answers over HTTPS with a valid certificate and the ownership
     * proof only this application can produce for that exact host. Timeouts, TLS errors,
     * non-2xx statuses, redirects and a missing or wrong proof all count as not proven.
     */
    public function proves(string $domain): bool;
}
