<?php

declare(strict_types=1);

namespace App\Filament\Resources\CateringInquiries\Support;

use App\Services\Settings\TenantSettings;

final class CateringEventTypeOptions
{
    /** @return array<string, string> */
    public static function make(TenantSettings $settings): array
    {
        $eventTypes = $settings->catering->eventTypes;

        return array_combine($eventTypes, $eventTypes) ?: [];
    }
}
