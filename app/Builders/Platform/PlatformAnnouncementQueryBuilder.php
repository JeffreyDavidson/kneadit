<?php

namespace App\Builders\Platform;

use App\Models\Platform\PlatformAnnouncement;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<PlatformAnnouncement> */
class PlatformAnnouncementQueryBuilder extends Builder
{
    public function active(): static
    {
        $this
            ->where('is_active', true)
            ->where(function (Builder $q): void {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });

        return $this;
    }
}
