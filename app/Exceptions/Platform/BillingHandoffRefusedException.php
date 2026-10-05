<?php

declare(strict_types=1);

namespace App\Exceptions\Platform;

use App\Models\Platform\Tenant;
use Illuminate\Contracts\Debug\ShouldntReport;
use RuntimeException;

/**
 * The billing handoff link cannot be used: unknown, already used, expired, or
 * its bakery no longer matches the owner it was issued for. Carries the bakery
 * when the link is known, so the refusal page can lead back to its admin.
 */
class BillingHandoffRefusedException extends RuntimeException implements ShouldntReport
{
    public function __construct(public readonly ?Tenant $tenant = null)
    {
        parent::__construct('This billing link is invalid or has expired.');
    }
}
