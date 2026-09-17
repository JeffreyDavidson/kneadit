<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Models\Platform\PlatformSetting;

class PlatformSettingsManager extends AbstractSettingsManager
{
    protected function cacheKey(): string
    {
        return 'platform';
    }

    protected function modelClass(): string
    {
        return PlatformSetting::class;
    }
}
