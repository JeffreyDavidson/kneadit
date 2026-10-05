<?php

namespace App\Console\Commands\Platform;

use App\Models\Platform\BillingHandoffToken;
use App\Models\Platform\ImpersonationToken;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('platform:prune-expired-tokens {--days=7 : Delete tokens that expired more than this many days ago}')]
#[Description('Delete single-use impersonation and billing handoff tokens that expired long ago, consumed or not')]
class PruneExpiredTokensCommand extends Command
{
    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $impersonation = ImpersonationToken::query()->where('expires_at', '<', $cutoff)->delete();
        $billingHandoff = BillingHandoffToken::query()->where('expires_at', '<', $cutoff)->delete();

        $this->info("Pruned {$impersonation} impersonation and {$billingHandoff} billing handoff tokens (cutoff: {$cutoff->toIso8601String()})");

        return self::SUCCESS;
    }
}
