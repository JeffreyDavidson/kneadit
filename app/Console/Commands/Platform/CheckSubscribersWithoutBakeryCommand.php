<?php

namespace App\Console\Commands\Platform;

use App\Enums\Staff\UserRole;
use App\Mail\Platform\SubscriberWithoutBakeryAlertMail;
use App\Models\Staff\User;
use App\Queries\Platform\SubscribersWithoutBakeryQuery;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('platform:check-subscribers-without-bakery')]
#[Description('Alert platform admins about paying accounts that have no bakery. The mail lists account ids only.')]
class CheckSubscribersWithoutBakeryCommand extends Command
{
    public function handle(SubscribersWithoutBakeryQuery $query): int
    {
        $userIds = $query->ids()->all();

        if ($userIds === []) {
            $this->info('Every paying account has a bakery.');

            return self::SUCCESS;
        }

        $admins = User::query()->where('role', UserRole::PlatformAdmin)->pluck('email')->filter()->all();
        if ($admins === []) {
            $this->warn('No platform admins found — nothing to alert.');

            return self::SUCCESS;
        }

        Mail::to($admins)->queue(new SubscriberWithoutBakeryAlertMail($userIds));
        $this->info(sprintf('%d paying account(s) without a bakery; alert queued to %d platform admin(s).', count($userIds), count($admins)));

        return self::SUCCESS;
    }
}
