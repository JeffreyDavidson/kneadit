<?php

namespace App\Builders\Platform;

use App\Models\Platform\AdminAuditLog;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<AdminAuditLog> */
class AdminAuditLogQueryBuilder extends Builder
{
    public function forAction(string $action): static
    {
        $this->where('action', $action);

        return $this;
    }

    public function forTarget(string $type, ?string $id = null): static
    {
        $this->where('target_type', $type);

        if ($id !== null) {
            $this->where('target_id', $id);
        }

        return $this;
    }

    public function recent(): static
    {
        $this->where('created_at', '>=', now()->subDays(config('analytics.recent_days', 30)));

        return $this;
    }
}
