<?php

declare(strict_types=1);

namespace App\Services\Platform\Contracts;

interface HttpsProbe
{
    /**
     * Whether the domain answers its health URL over HTTPS with a valid certificate
     * and a 2xx status. Timeouts, TLS errors, other statuses and redirects all count
     * as not serving.
     */
    public function serves(string $domain): bool;
}
