<?php

namespace App\Actions\Platform;

use App\Events\Platform\ScheduledCheckinDue;
use App\Models\Operations\CheckinLog;
use App\Models\Operations\ScheduledCheckin;
use App\Models\Platform\Tenant;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCheckins
{
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
                $alreadySent = CheckinLog::query()
                    ->where('checkin_id', $checkin->id)
                    ->where('tenant_id', $tenant->id)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                if (! $tenant->email) {
                    $skippedNoEmail++;

                    continue;
                }

                try {
                    event(new ScheduledCheckinDue($tenant->email, $checkin->body, $checkin->subject));

                    CheckinLog::query()->create([
                        'checkin_id' => $checkin->id,
                        'tenant_id' => $tenant->id,
                        'sent_at' => now(),
                    ]);

                    $sent++;
                } catch (\Exception $e) {
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
