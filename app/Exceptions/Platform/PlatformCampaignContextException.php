<?php

declare(strict_types=1);

namespace App\Exceptions\Platform;

use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

class PlatformCampaignContextException extends RuntimeException implements ShouldntReport
{
    public static function insideTenant(): self
    {
        return new self('Platform email campaigns can only be sent from the central application.');
    }
}
