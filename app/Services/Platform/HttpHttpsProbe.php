<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Services\Platform\Contracts\HttpsProbe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final readonly class HttpHttpsProbe implements HttpsProbe
{
    public function __construct(
        private CustomDomainProof $proof,
    ) {}

    public function proves(string $domain): bool
    {
        try {
            $response = Http::timeout(5)
                ->connectTimeout(3)
                ->withoutRedirecting()
                ->get("https://{$domain}/".CustomDomainProof::PATH);
        } catch (ConnectionException) {
            return false;
        }

        return $response->successful() && $this->proof->matches($domain, $response->body());
    }
}
