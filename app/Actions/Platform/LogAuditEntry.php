<?php

namespace App\Actions\Platform;

use App\Models\Platform\AdminAuditLog;

class LogAuditEntry
{
    /** @param array<string, mixed>|null $metadata */
    public function __invoke(
        string $action,
        string $description,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $metadata = null,
    ): AdminAuditLog {
        return AdminAuditLog::query()->create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
