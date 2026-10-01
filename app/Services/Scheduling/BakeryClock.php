<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Services\Settings\TenantSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/**
 * "Now" and "today" for the bakery (the `timezone` order setting, UTC until
 * set). Always reflects the current tenant: the settings are resolved on every
 * call, so an instance created before a tenant is initialized (a scheduled
 * command's engagement, say) follows each tenant it runs for. today() is the
 * bakery-local date as a plain date value (midnight in the app timezone), the
 * same shape a date column loads as, so comparisons and day counts against
 * stored dates line up.
 */
final readonly class BakeryClock
{
    public function now(): Carbon
    {
        return Date::now(resolve(TenantSettings::class)->orders->timezone);
    }

    public function today(): Carbon
    {
        return Date::parse($this->now()->toDateString());
    }
}
