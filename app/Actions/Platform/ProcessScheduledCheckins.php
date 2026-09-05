<?php

namespace App\Actions\Platform;

use App\Events\Platform\ScheduledCheckinDue;
use App\Models\Operations\CheckinLog;
use App\Models\Operations\ScheduledCheckin;
use App\Models\Platform\Tenant;
use App\Services\Tenants\TenantUrlGenerator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCheckins
{
    public function __construct(private readonly TenantUrlGenerator $tenantUrls) {}

    /** @return array{sent: int, skipped_no_email: int, failures: int, no_active_checkins: bool} */
    public function __invoke(): array
    {
        $checkins = ScheduledCheckin::query()->where('is_active', true)->get();

        if ($checkins->isEmpty()) {
            return [
                'sent' => 0,
                'skipped_no_email' => 0,
                'failures' => 0,
                'no_active_checkins' => true,
            ];
        }

        $sent = 0;
        $skippedNoEmail = 0;
        $failures = 0;

        foreach ($checkins as $checkin) {
            $targetDate = Date::today()->subDays($checkin->days_after_signup);
            $tenants = Tenant::query()->whereDate('created_at', $targetDate)->cursor();

            /** @var Tenant $tenant */
            foreach ($tenants as $tenant) {
                if (! $tenant->email) {
                    $skippedNoEmail++;

                    continue;
                }

                $log = null;

                try {
                    $log = CheckinLog::query()->createOrFirst([
                        'checkin_id' => $checkin->id,
                        'tenant_id' => $tenant->id,
                    ], [
                        'sent_at' => now(),
                    ]);

                    if (! $log->wasRecentlyCreated) {
                        continue;
                    }

                    event(new ScheduledCheckinDue(
                        tenantEmail: $tenant->email,
                        body: $checkin->body,
                        subject: $checkin->subject,
                        bakerName: $tenant->name,
                        tenantId: $tenant->id,
                        adminUrl: $this->tenantUrls->admin($tenant),
                    ));

                    $sent++;
                } catch (\Exception $e) {
                    if (isset($log) && $log->wasRecentlyCreated) {
                        $log->delete();
                    }

                    Log::error('Failed to send checkin to tenant', [
                        'tenant' => $tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                    $failures++;
                }
            }
        }

        return [
            'sent' => $sent,
            'skipped_no_email' => $skippedNoEmail,
            'failures' => $failures,
            'no_active_checkins' => false,
        ];
    }
}
