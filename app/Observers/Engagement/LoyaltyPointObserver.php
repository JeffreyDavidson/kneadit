<?php

declare(strict_types=1);

namespace App\Observers\Engagement;

use App\Models\Engagement\LoyaltyPoint;

class LoyaltyPointObserver
{
    public function creating(LoyaltyPoint $model): void
    {
        $model->created_at ??= now();
    }
}
