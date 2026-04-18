<?php

namespace App\Console\Commands\Platform;

use App\Actions\Platform\ProcessScheduledCheckins;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('checkins:send')]
#[Description('Send scheduled check-in emails to tenants based on their signup date')]
class SendScheduledCheckinsCommand extends Command
{
    public function handle(ProcessScheduledCheckins $action): int
    {
        $summary = $action();

        if ($summary['no_active_checkins']) {
            $this->info('No active check-ins found.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Logged %d check-in(s).', $summary['sent']));

        return $summary['failures'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
