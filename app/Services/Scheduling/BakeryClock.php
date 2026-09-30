<?php

declare(strict_types=1);

namespace App\Services\Scheduling;

use App\Services\Settings\TenantSettings;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

/**
 * "Now" and "today" for the bakery (the `timezone` order setting, UTC until
 * set). today() is the bakery-local date as a plain date value (midnight in
 * the app timezone), the same shape a date column loads as, so comparisons
 * and day counts against stored dates line up.
 */
final readonly class BakeryClock
{
    public function __construct(
        private TenantSettings $settings,
    ) {}

    public function now(): Carbon
    {
        return Date::now($this->settings->orders->timezone);
    }

    public function today(): Carbon
    {
        return Date::parse($this->now()->toDateString());
    }
}
