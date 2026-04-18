<?php

namespace App\Console\Commands\Platform;

use App\Actions\Platform\ProcessTrialExpirations;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('trial:check')]
#[Description('Send trial expiration reminder emails and restrict expired accounts')]
class CheckTrialExpirationsCommand extends Command
{
    public function handle(ProcessTrialExpirations $action): int
    {
        $summary = $action();

        $this->info(sprintf(
            'Trial check: %d reminders sent, %d storefronts paused, %d failures',
            $summary['reminders'],
            $summary['pausings'],
            $summary['failures'],
        ));

        return $summary['failures'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
