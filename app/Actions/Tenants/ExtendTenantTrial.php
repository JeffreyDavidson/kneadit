<?php

namespace App\Actions\Tenants;

use App\Actions\Platform\ResumeTenant;
use App\Models\Platform\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class ExtendTenantTrial
{
    public function __construct(private readonly ResumeTenant $resumeTenant) {}

    public function __invoke(Tenant $tenant, int $days = 30): Carbon
    {
        $currentEnd = $tenant->trial_ends_at ? Date::parse($tenant->trial_ends_at) : now();
        $newEnd = $currentEnd->isPast() ? now()->addDays($days) : $currentEnd->addDays($days);

        $tenant->update(['trial_ends_at' => $newEnd]);

        // A bakery paused when its trial ended runs again once the trial is back in the future.
        if ($newEnd->isFuture()) {
            ($this->resumeTenant)($tenant);
        }

        return $newEnd;
    }
}
