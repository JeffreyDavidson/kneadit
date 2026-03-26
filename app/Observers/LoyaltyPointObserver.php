<?php

namespace App\Observers;

use App\Models\LoyaltyPoint;

class LoyaltyPointObserver
{
    public function creating(LoyaltyPoint $model): void
    {
        $model->created_at = $model->created_at ?? now();
    }
}
