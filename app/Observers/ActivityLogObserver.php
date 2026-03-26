<?php

namespace App\Observers;

use App\Models\ActivityLog;

class ActivityLogObserver
{
    public function creating(ActivityLog $log): void
    {
        $log->created_at = $log->created_at ?? now();
    }
}
