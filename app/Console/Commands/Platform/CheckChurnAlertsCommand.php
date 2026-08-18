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
            resolve(LogAuditEntry::class)(
                action: 'churn_alert',
                description: "{$alert['type_label']}: {$alert['name']} — {$alert['description']}",
                targetType: 'tenant',
                targetId: (string) $alert['tenant_id'],
                metadata: ['type' => $alert['type'], 'severity' => $alert['severity']],
            );
        }

        $this->info("Churn check complete. {$alerts->count()} alert(s) logged.");

        return self::SUCCESS;
    }
}
