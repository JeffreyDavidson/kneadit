<?php

namespace App\Filament\Pages\Engagement;

use App\Actions\Tenants\GenerateReferralCode;
use App\Filament\Concerns\RequiresManagerRole;
use App\Models\Customers\Referral;
use App\Models\Platform\Tenant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;

class ReferralProgram extends Page
{
    use RequiresManagerRole;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Referral Program';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.engagement.referral-program';

    protected static ?string $title = 'Referral Program';

    public function getReferralCode(): string
    {
        return resolve(GenerateReferralCode::class)($this->tenant());
    }

    public function getReferralLink(): string
    {
        return Config::string('app.url') . '/ref/' . $this->getReferralCode();
    }

    public function getTotalReferrals(): int
    {
        return Referral::query()->where('referrer_tenant_id', $this->tenant()->id)
            ->whereNotNull('referred_tenant_id')
            ->count();
    }

    public function getCompletedReferrals(): int
    {
        return Referral::query()->where('referrer_tenant_id', $this->tenant()->id)
            ->successful()
            ->count();
    }

    public function getMonthsEarned(): int
    {
        return (int) Referral::query()->where('referrer_tenant_id', $this->tenant()->id)
            ->rewarded()
            ->sum('reward_months');
    }

    /** @return Collection<int, Referral> */
    public function getReferrals(): Collection
    {
        return Referral::query()->where('referrer_tenant_id', $this->tenant()->id)
            ->whereNotNull('referred_tenant_id')->latest()
            ->get();
    }

    private function tenant(): Tenant
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \LogicException('A tenant must be initialized to view the referral program.');
        }

        return $tenant;
    }
}
