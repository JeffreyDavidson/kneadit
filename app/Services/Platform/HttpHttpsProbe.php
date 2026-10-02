<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Services\Platform\Contracts\HttpsProbe;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final readonly class HttpHttpsProbe implements HttpsProbe
{
    public function serves(string $domain): bool
    {
        try {
            return Http::timeout(5)
                ->connectTimeout(3)
                ->withoutRedirecting()
                ->get("https://{$domain}/up")
                ->successful();
        } catch (ConnectionException) {
            return false;
        }
    }
}
