<?php

namespace App\Console\Commands;

use App\Models\CheckinLog;
use App\Models\ScheduledCheckin;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

class SendScheduledCheckins extends Command
{
    protected $signature = 'checkins:send';

    protected $description = 'Send scheduled check-in emails to tenants based on their signup date';

    public function handle(): int
    {
        $checkins = ScheduledCheckin::query()->where('is_active', true)->get();

        if ($checkins->isEmpty()) {
            $this->info('No active check-ins found.');

            return self::SUCCESS;
        }

        $sentCount = 0;

        foreach ($checkins as $checkin) {
            $targetDate = Date::today()->subDays($checkin->days_after_signup);

            $tenants = Tenant::query()->whereDate('created_at', $targetDate)->get();

            foreach ($tenants as $tenant) {
                $alreadySent = CheckinLog::query()->where('checkin_id', $checkin->id)
                    ->where('tenant_id', $tenant->id)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                if (! $tenant->email) {
                    $this->warn("Skipping tenant {$tenant->id} — no email address.");

                    continue;
                }

                try {
                    Mail::raw($checkin->body, function (Message $message) use ($tenant, $checkin) {
                        $message->to($tenant->email)
                            ->subject($checkin->subject);
                    });

                    CheckinLog::query()->create([
                        'checkin_id' => $checkin->id,
                        'tenant_id' => $tenant->id,
                        'sent_at' => now(),
                    ]);

                    $sentCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to send checkin to {$tenant->id}: {$e->getMessage()}");
                }
            }
        }

        $this->info("Logged {$sentCount} check-in(s).");

        return self::SUCCESS;
    }
}
