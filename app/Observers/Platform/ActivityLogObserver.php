<?php

namespace App\Observers\Platform;

use App\Models\Operations\ActivityLog;

class ActivityLogObserver
{
    public function creating(ActivityLog $log): void
    {
        $log->created_at = $log->created_at ?? now();
    }
}
