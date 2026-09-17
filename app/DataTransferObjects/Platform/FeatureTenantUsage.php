<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Platform;

final readonly class FeatureTenantUsage
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public int $total,
    ) {}

    /** @return array{tenant_id: string, name: string, total: int} */
    public function toArray(): array
    {
        return ['tenant_id' => $this->tenantId, 'name' => $this->name, 'total' => $this->total];
    }
}
