<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Config;

class VisitorIdentifier
{
    public function fromSessionId(string $sessionId): string
    {
        return hash_hmac('sha256', $sessionId, Config::string('app.key'));
    }
}
