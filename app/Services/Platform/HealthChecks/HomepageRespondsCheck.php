<?php

namespace App\Services\Platform\HealthChecks;

use App\Services\Platform\HealthChecks\Contracts\HealthCheck;
use Illuminate\Support\Facades\Http;

class HomepageRespondsCheck implements HealthCheck
{
    public function run(): HealthCheckResult
    {
        try {
            $response = Http::timeout(10)->connectTimeout(3)->retry(2, 100)->get(config('app.url'));

            if (! $response->successful()) {
                return HealthCheckResult::fail("Homepage returned status {$response->status()}");
            }

            return HealthCheckResult::pass('Homepage responds (' . $response->status() . ')');
        } catch (\Exception $e) {
            return HealthCheckResult::fail("Homepage unreachable: {$e->getMessage()}");
        }
    }
}
