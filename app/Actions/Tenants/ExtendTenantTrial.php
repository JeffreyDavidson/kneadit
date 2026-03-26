<?php

namespace App\Actions\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

class ExtendTenantTrial
{
    public function __invoke(Tenant $tenant, int $days = 30): Carbon
    {
        $currentEnd = $tenant->trial_ends_at ? Date::parse($tenant->trial_ends_at) : now();
        $newEnd = $currentEnd->isPast() ? now()->addDays($days) : $currentEnd->addDays($days);

        $tenant->update(['trial_ends_at' => $newEnd]);

        return $newEnd;
    }
}
