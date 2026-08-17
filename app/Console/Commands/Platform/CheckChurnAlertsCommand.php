<?php

namespace App\Console\Commands\Platform;

use App\Actions\Platform\LogAuditEntry;
use App\Services\Tenants\ChurnAlertService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('churn:check')]
#[Description('Check for churn risk indicators and log alerts')]
class CheckChurnAlertsCommand extends Command
{
    public function handle(ChurnAlertService $churnAlertService): int
    {
        $alerts = $churnAlertService->getAlerts();

        foreach ($alerts as $alert) {
            $typeLabel = $this->stringValue($alert['type_label'] ?? null);
            $name = $this->stringValue($alert['name'] ?? null);
            $description = $this->stringValue($alert['description'] ?? null);
            $tenantId = $this->stringValue($alert['tenant_id'] ?? null);

            resolve(LogAuditEntry::class)(
                action: 'churn_alert',
                description: "{$typeLabel}: {$name} — {$description}",
                targetType: 'tenant',
                targetId: $tenantId,
                metadata: ['type' => $alert['type'], 'severity' => $alert['severity']],
            );
        }

        $this->info("Churn check complete. {$alerts->count()} alert(s) logged.");

        return self::SUCCESS;
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) || is_int($value) ? "{$value}" : '';
    }
}
