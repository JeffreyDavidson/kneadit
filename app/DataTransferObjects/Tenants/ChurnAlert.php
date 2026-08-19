<?php

namespace App\DataTransferObjects\Tenants;

use App\Enums\Tenants\ChurnAlertType;
use App\Enums\Tenants\ChurnSeverity;

final readonly class ChurnAlert
{
    public function __construct(
        public string $tenantId,
        public string $name,
        public ChurnAlertType $type,
        public string $description,
        public int $daysSinceSignup,
        public ChurnSeverity $severity,
    ) {}

    /** @return array{tenant_id: string, name: string, type: string, type_label: string, description: string, days_since_signup: int, severity: string} */
    public function toArray(): array
    {
        return [
            'tenant_id' => $this->tenantId,
            'name' => $this->name,
            'type' => $this->type->value,
            'type_label' => $this->type->getLabel(),
            'description' => $this->description,
            'days_since_signup' => $this->daysSinceSignup,
            'severity' => $this->severity->value,
        ];
    }
}
